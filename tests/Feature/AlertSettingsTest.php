<?php

namespace Tests\Feature;

use App\Models\AlertConfig;
use App\Models\Alvara;
use App\Models\Empresa;
use App\Models\TipoAlvara;
use App\Models\User;
use App\Notifications\VencimentoAlvaraNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
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
}
