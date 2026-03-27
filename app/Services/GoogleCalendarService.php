<?php

namespace App\Services;

use App\Models\Alvara;
use App\Models\User;
use Carbon\CarbonInterface;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Google\Service\Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class GoogleCalendarService
{
    private const CALENDAR_SCOPE = 'https://www.googleapis.com/auth/calendar.events';

    private const EVENT_RED_COLOR_ID = '11';

    public const STATUS_CONNECTED = 'connected';

    public const STATUS_DISCONNECTED = 'disconnected';

    public const STATUS_RECONNECT_REQUIRED = 'reconnect_required';

    public const STATUS_MISCONFIGURED = 'misconfigured';

    public function isConfigured(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'))
            && filled(config('services.google.redirect'));
    }

    public function hasStoredConnection(?User $user): bool
    {
        return $user instanceof User && $user->hasGoogleConnection();
    }

    public function getConnectionStatus(?User $user): string
    {
        if (! $this->isConfigured()) {
            return self::STATUS_MISCONFIGURED;
        }

        if (! $this->hasStoredConnection($user)) {
            return self::STATUS_DISCONNECTED;
        }

        if ($this->tokenExpired($user) && blank($user->google_refresh_token)) {
            return self::STATUS_RECONNECT_REQUIRED;
        }

        try {
            $calendarService = new Calendar($this->makeClient($user));
            $calendarService->events->listEvents(
                $user->google_calendar_id ?: config('services.google.calendar_id', 'primary'),
                ['maxResults' => 1, 'singleEvents' => true]
            );

            return self::STATUS_CONNECTED;
        } catch (Exception $exception) {
            if ($this->isMisconfiguredGoogleException($exception)) {
                return self::STATUS_MISCONFIGURED;
            }

            Log::warning('Conexao Google Calendar exige reconexao.', [
                'user_id' => $user?->id,
                'error' => $exception->getMessage(),
            ]);

            return self::STATUS_RECONNECT_REQUIRED;
        } catch (\Throwable $exception) {
            Log::warning('Falha ao validar conexao com Google Calendar.', [
                'user_id' => $user?->id,
                'error' => $exception->getMessage(),
            ]);

            return self::STATUS_RECONNECT_REQUIRED;
        }
    }

    public function redirectToProvider(): RedirectResponse
    {
        if (! $this->isConfigured()) {
            throw new \RuntimeException('As credenciais do Google Calendar ainda nao foram configuradas.');
        }

        return Socialite::driver('google')
            ->scopes([self::CALENDAR_SCOPE])
            ->with([
                'access_type' => 'offline',
                'prompt' => 'consent',
                'include_granted_scopes' => 'true',
            ])
            ->redirect();
    }

    public function handleCallback(?User $authenticatedUser): User
    {
        if (! $this->isConfigured()) {
            throw new \RuntimeException('As credenciais do Google Calendar ainda nao foram configuradas.');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException $exception) {
            Log::warning('Google OAuth retornou com state invalido. Tentando fluxo stateless.', [
                'user_id' => $authenticatedUser?->id,
                'error' => $exception->getMessage(),
            ]);

            $googleUser = Socialite::driver('google')->stateless()->user();
        }

        $user = $this->resolveCallbackUser($authenticatedUser, $googleUser);

        $user->forceFill([
            'google_id' => $googleUser->getId(),
            'google_token' => $googleUser->token,
            'google_refresh_token' => $googleUser->refreshToken ?: $user->google_refresh_token,
            'google_token_expires_at' => $googleUser->expiresIn ? now()->addSeconds((int) $googleUser->expiresIn) : null,
            'google_calendar_id' => $user->google_calendar_id ?: config('services.google.calendar_id', 'primary'),
        ])->save();

        return $user;
    }

    public function disconnect(User $user): void
    {
        $user->forceFill([
            'google_id' => null,
            'google_token' => null,
            'google_refresh_token' => null,
            'google_token_expires_at' => null,
            'google_calendar_id' => config('services.google.calendar_id', 'primary'),
        ])->save();
    }

    public function createEventForAlert(User $user, Alvara $alvara, int $daysBefore): void
    {
        if (! $this->isConfigured() || ! $this->hasStoredConnection($user)) {
            return;
        }

        $calendarService = new Calendar($this->makeClient($user));
        $event = new Event;
        $event->setColorId(self::EVENT_RED_COLOR_ID);
        $event->setSummary($this->buildEventTitle($alvara));
        $event->setDescription($this->buildEventDescription($alvara, $daysBefore));
        $event->setStart($this->buildEventDateTime($alvara, $daysBefore, 8));
        $event->setEnd($this->buildEventDateTime($alvara, $daysBefore, 9));

        $calendarService->events->insert(
            $user->google_calendar_id ?: config('services.google.calendar_id', 'primary'),
            $event
        );
    }

    private function makeClient(User $user): Client
    {
        $client = new Client;
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));
        $client->setAccessType('offline');
        $client->setScopes([self::CALENDAR_SCOPE]);

        if ($this->tokenExpired($user) && filled($user->google_refresh_token)) {
            $token = $client->fetchAccessTokenWithRefreshToken($user->google_refresh_token);

            if (isset($token['error'])) {
                throw new \RuntimeException('Nao foi possivel renovar o token do Google. Reconecte sua conta.');
            }

            $user->forceFill([
                'google_token' => $token['access_token'] ?? $user->google_token,
                'google_refresh_token' => $token['refresh_token'] ?? $user->google_refresh_token,
                'google_token_expires_at' => isset($token['expires_in']) ? now()->addSeconds((int) $token['expires_in']) : $user->google_token_expires_at,
            ])->save();
        }

        if (! filled($user->google_token)) {
            throw new \RuntimeException('Usuario sem access token Google valido.');
        }

        $client->setAccessToken($user->google_token);

        return $client;
    }

    private function tokenExpired(User $user): bool
    {
        return $user->google_token_expires_at instanceof CarbonInterface
            && $user->google_token_expires_at->isPast();
    }

    private function isMisconfiguredGoogleException(Exception $exception): bool
    {
        $message = $exception->getMessage();

        return str_contains($message, 'SERVICE_DISABLED')
            || str_contains($message, 'accessNotConfigured')
            || str_contains($message, 'Google Calendar API has not been used');
    }

    private function buildEventTitle(Alvara $alvara): string
    {
        return sprintf(
            'Renovar alvara: %s - %s',
            $alvara->tipo,
            $alvara->empresa?->nome ?? 'Empresa'
        );
    }

    private function buildEventDescription(Alvara $alvara, int $daysBefore): string
    {
        $lines = [
            'Evento criado automaticamente pelo sistema de controle de alvaras.',
            sprintf('Empresa: %s', $alvara->empresa?->nome ?? 'Nao informada'),
            sprintf('Tipo: %s', $alvara->tipo),
            sprintf('Numero: %s', $alvara->numero),
            sprintf('Data de vencimento: %s', optional($alvara->data_vencimento)->format('d/m/Y')),
            sprintf('Antecedencia aplicada: %d dias', $daysBefore),
        ];

        if (Route::has('alvaras.show')) {
            try {
                $lines[] = sprintf('Link no sistema: %s', route('alvaras.show', $alvara));
            } catch (\Throwable $exception) {
                Log::warning('Nao foi possivel gerar o link do alvara para o evento Google.', [
                    'alvara_id' => $alvara->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return implode(PHP_EOL, $lines);
    }

    private function buildEventDateTime(Alvara $alvara, int $daysBefore, int $hour): EventDateTime
    {
        $timezone = config('services.google.timezone', 'America/Porto_Velho');
        $eventMoment = Carbon::parse($alvara->data_vencimento->toDateString(), $timezone)
            ->subDays($daysBefore)
            ->setTime($hour, 0, 0);

        $eventDateTime = new EventDateTime;
        $eventDateTime->setDateTime($eventMoment->toRfc3339String());
        $eventDateTime->setTimeZone($timezone);

        return $eventDateTime;
    }

    private function resolveCallbackUser(?User $authenticatedUser, object $googleUser): User
    {
        if ($authenticatedUser instanceof User) {
            return $authenticatedUser;
        }

        $resolvedUser = User::query()
            ->where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if (! $resolvedUser) {
            throw new \RuntimeException('Nao foi possivel identificar a conta local para concluir a conexao com o Google.');
        }

        return $resolvedUser;
    }
}
