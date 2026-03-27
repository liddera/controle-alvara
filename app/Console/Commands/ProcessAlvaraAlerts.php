<?php

namespace App\Console\Commands;

use App\Models\AlertConfig;
use App\Models\Alvara;
use App\Notifications\VencimentoAlvaraNotification;
use App\Services\GoogleCalendarService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class ProcessAlvaraAlerts extends Command
{
    protected $signature = 'alerts:process';

    protected $description = 'Processa e envia alertas de vencimento de alvarás baseados nas configurações dos usuários';

    public function handle(GoogleCalendarService $googleCalendarService)
    {
        $this->info('Iniciando processamento de alertas...');

        // Buscamos todas as configurações de alerta ativas
        $configs = AlertConfig::where('is_active', true)
            ->with('user:id,email,name,google_id,google_token,google_refresh_token,google_calendar_id,google_token_expires_at')
            ->get();

        foreach ($configs as $config) {
            if (! $config->user) {
                continue;
            }

            $targetDate = Carbon::today()->addDays($config->days_before);

            // Buscamos os alvarás que vencem na data alvo para o owner da configuração
            $query = Alvara::where('owner_id', $config->owner_id)
                ->whereDate('data_vencimento', $targetDate);

            // Se a configuração for específica para um tipo de alvará
            if ($config->tipo_alvara_id) {
                $query->where('tipo_alvara_id', $config->tipo_alvara_id);
            }

            $alvaras = $query->with('empresa')->get();

            $primaryEmail = strtolower($config->user->email);
            $additionalEmails = collect($config->recipient_emails ?? [])
                ->filter(fn ($email) => filled($email))
                ->map(fn ($email) => strtolower(trim((string) $email)))
                ->reject(fn ($email) => $email === $primaryEmail)
                ->unique()
                ->values();

            foreach ($alvaras as $alvara) {
                // Notificamos o usuário da configuração
                $config->user->notify(new VencimentoAlvaraNotification($alvara, $config->days_before));

                $this->line("Notificação enviada para {$config->user->email} sobre o alvará {$alvara->numero} ({$config->days_before} dias antes)");

                if ($googleCalendarService->hasStoredConnection($config->user)) {
                    try {
                        $googleCalendarService->createEventForAlert($config->user, $alvara, $config->days_before);

                        $this->line("Evento Google criado para {$config->user->email} sobre o alvará {$alvara->numero} ({$config->days_before} dias antes)");
                    } catch (\Throwable $exception) {
                        $this->warn("Falha ao criar evento Google para {$config->user->email}: {$exception->getMessage()}");
                    }
                }

                foreach ($additionalEmails as $email) {
                    Notification::route('mail', $email)
                        ->notify(new VencimentoAlvaraNotification($alvara, $config->days_before));

                    $this->line("Notificação adicional enviada para {$email} sobre o alvará {$alvara->numero} ({$config->days_before} dias antes)");
                }
            }
        }

        $this->info('Processamento concluído!');
    }
}
