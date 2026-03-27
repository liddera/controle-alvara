<?php

namespace Tests\Feature;

use App\Models\AlertConfig;
use App\Models\Alvara;
use App\Models\Empresa;
use App\Models\TipoAlvara;
use App\Models\User;
use App\Notifications\VencimentoAlvaraNotification;
use App\Services\GoogleCalendarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class AlertSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_email_is_visible_on_alert_settings_page(): void
    {
        $user = User::factory()->create([
            'email' => 'dono@example.com',
        ]);

        $response = $this->actingAs($user)->get(route('profile.alerts'));

        $response->assertOk();
        $response->assertSee('dono@example.com');
        $response->assertSee('Dono:');
    }

    public function test_google_calendar_section_is_visible_on_alert_settings_page(): void
    {
        $user = User::factory()->create([
            'email' => 'dono@example.com',
        ]);

        $response = $this->actingAs($user)->get(route('profile.alerts'));

        $response->assertOk();
        $response->assertSee('Google Agenda');
        $response->assertSee('Conectar com Google');
        $response->assertSee('Indisponivel');
    }

    public function test_google_calendar_section_shows_reconnect_status_when_required(): void
    {
        $user = User::factory()->create([
            'email' => 'dono@example.com',
        ]);

        $mock = Mockery::mock(GoogleCalendarService::class);
        $mock->shouldReceive('getConnectionStatus')
            ->once()
            ->withArgs(fn (User $currentUser) => $currentUser->is($user))
            ->andReturn(GoogleCalendarService::STATUS_RECONNECT_REQUIRED);

        $this->app->instance(GoogleCalendarService::class, $mock);

        $response = $this->actingAs($user)->get(route('profile.alerts'));

        $response->assertOk();
        $response->assertSee('Reconectar Google');
        $response->assertSee('A conexao com o Google nao esta mais valida');
    }

    public function test_alert_config_stores_additional_recipients_without_owner_email_duplicates(): void
    {
        $user = User::factory()->create([
            'email' => 'dono@example.com',
        ]);

        $tipoAlvara = TipoAlvara::create([
            'nome' => 'Alvara Sanitario',
            'slug' => 'alvara-sanitario',
        ]);

        $response = $this->actingAs($user)->post(route('profile.alerts.store'), [
            'tipo_alvara_id' => $tipoAlvara->id,
            'days_before' => 15,
            'recipient_emails' => [
                'FINANCEIRO@EXAMPLE.COM',
                'financeiro@example.com',
                ' dono@example.com ',
                'juridico@example.com',
            ],
        ]);

        $response->assertSessionHasNoErrors();

        $config = AlertConfig::query()->firstOrFail();

        $this->assertSame($user->id, $config->user_id);
        $this->assertSame([
            'financeiro@example.com',
            'juridico@example.com',
        ], $config->recipient_emails);
    }

    public function test_process_command_sends_notifications_to_owner_and_additional_recipients(): void
    {
        $user = User::factory()->create([
            'email' => 'dono@example.com',
            'owner_id' => null,
        ]);

        $tipoAlvara = TipoAlvara::create([
            'nome' => 'Alvara de Funcionamento',
            'slug' => 'alvara-funcionamento',
        ]);

        $empresa = Empresa::create([
            'user_id' => $user->id,
            'owner_id' => $user->id,
            'nome' => 'Empresa Teste',
            'cnpj' => '12.345.678/0001-90',
            'responsavel' => 'Responsavel',
            'telefone' => '69999999999',
            'email' => 'contato@empresa.com',
        ]);

        $alvara = Alvara::create([
            'empresa_id' => $empresa->id,
            'user_id' => $user->id,
            'owner_id' => $user->id,
            'tipo_alvara_id' => $tipoAlvara->id,
            'tipo' => 'Alvara de Funcionamento',
            'numero' => 'ALV-1000/2026',
            'data_emissao' => now()->subDays(30)->toDateString(),
            'data_vencimento' => now()->toDateString(),
            'status' => 'proximo',
        ]);

        AlertConfig::create([
            'owner_id' => $user->id,
            'user_id' => $user->id,
            'tipo_alvara_id' => null,
            'days_before' => 0,
            'is_active' => true,
            'recipient_emails' => [
                'financeiro@example.com',
                'dono@example.com',
                'juridico@example.com',
                'financeiro@example.com',
            ],
        ]);

        Notification::fake();

        Artisan::call('alerts:process');

        Notification::assertSentToTimes($user, VencimentoAlvaraNotification::class, 1);
        Notification::assertSentOnDemandTimes(VencimentoAlvaraNotification::class, 2);

        Notification::assertSentOnDemand(
            VencimentoAlvaraNotification::class,
            function ($notification, array $channels, object $notifiable) use ($alvara) {
                return $notification->alvara->id === $alvara->id
                    && in_array('mail', $channels, true)
                    && ($notifiable->routes['mail'] ?? null) === 'financeiro@example.com';
            }
        );

        Notification::assertSentOnDemand(
            VencimentoAlvaraNotification::class,
            function ($notification, array $channels, object $notifiable) use ($alvara) {
                return $notification->alvara->id === $alvara->id
                    && in_array('mail', $channels, true)
                    && ($notifiable->routes['mail'] ?? null) === 'juridico@example.com';
            }
        );
    }

    public function test_google_callback_stores_google_connection_data(): void
    {
        config()->set('services.google.client_id', 'client-id');
        config()->set('services.google.client_secret', 'client-secret');
        config()->set('services.google.redirect', 'http://localhost/google/callback');
        config()->set('services.google.calendar_id', 'primary');

        $user = User::factory()->create([
            'google_id' => null,
            'google_token' => null,
            'google_refresh_token' => null,
        ]);

        $socialiteUser = (new SocialiteUser)->map([
            'id' => 'google-user-123',
            'name' => 'Google User',
            'email' => 'dono@example.com',
        ]);
        $socialiteUser->token = 'google-access-token';
        $socialiteUser->refreshToken = 'google-refresh-token';
        $socialiteUser->expiresIn = 3600;

        Socialite::shouldReceive('driver')->once()->with('google')->andReturnSelf();
        Socialite::shouldReceive('user')->once()->andReturn($socialiteUser);

        $response = $this->actingAs($user)->get(route('google.callback'));

        $response->assertRedirect(route('profile.alerts'));

        $user->refresh();

        $this->assertSame('google-user-123', $user->google_id);
        $this->assertSame('google-access-token', $user->google_token);
        $this->assertSame('google-refresh-token', $user->google_refresh_token);
        $this->assertSame('primary', $user->google_calendar_id);
        $this->assertNotNull($user->google_token_expires_at);
    }

    public function test_process_command_creates_google_event_for_connected_user(): void
    {
        $user = User::factory()->create([
            'email' => 'dono@example.com',
            'owner_id' => null,
            'google_id' => 'google-user-123',
            'google_token' => 'google-access-token',
        ]);

        $tipoAlvara = TipoAlvara::create([
            'nome' => 'Alvara de Funcionamento',
            'slug' => 'alvara-funcionamento',
        ]);

        $empresa = Empresa::create([
            'user_id' => $user->id,
            'owner_id' => $user->id,
            'nome' => 'Empresa Teste',
            'cnpj' => '12.345.678/0001-90',
            'responsavel' => 'Responsavel',
            'telefone' => '69999999999',
            'email' => 'contato@empresa.com',
        ]);

        $alvara = Alvara::create([
            'empresa_id' => $empresa->id,
            'user_id' => $user->id,
            'owner_id' => $user->id,
            'tipo_alvara_id' => $tipoAlvara->id,
            'tipo' => 'Alvara de Funcionamento',
            'numero' => 'ALV-1001/2026',
            'data_emissao' => now()->subDays(30)->toDateString(),
            'data_vencimento' => now()->toDateString(),
            'status' => 'proximo',
        ]);

        AlertConfig::create([
            'owner_id' => $user->id,
            'user_id' => $user->id,
            'tipo_alvara_id' => null,
            'days_before' => 0,
            'is_active' => true,
            'recipient_emails' => [],
        ]);

        Notification::fake();

        $mock = Mockery::mock(GoogleCalendarService::class);
        $mock->shouldReceive('hasStoredConnection')
            ->once()
            ->withArgs(fn (User $connectedUser) => $connectedUser->is($user))
            ->andReturn(true);
        $mock->shouldReceive('createEventForAlert')
            ->once()
            ->withArgs(fn (User $connectedUser, Alvara $targetAlvara, int $daysBefore) => $connectedUser->is($user)
                && $targetAlvara->is($alvara)
                && $daysBefore === 0);

        $this->app->instance(GoogleCalendarService::class, $mock);

        Artisan::call('alerts:process');

        Notification::assertSentToTimes($user, VencimentoAlvaraNotification::class, 1);
    }
}
