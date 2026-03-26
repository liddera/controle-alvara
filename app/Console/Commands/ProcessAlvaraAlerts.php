<?php

namespace App\Console\Commands;

use App\Models\Alvara;
use App\Models\AlertConfig;
use App\Notifications\VencimentoAlvaraNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class ProcessAlvaraAlerts extends Command
{
    protected $signature = 'alerts:process';
    protected $description = 'Processa e envia alertas de vencimento de alvarás baseados nas configurações dos usuários';

    public function handle()
    {
        $this->info('Iniciando processamento de alertas...');

        // Buscamos todas as configurações de alerta ativas
        $configs = AlertConfig::where('is_active', true)
            ->with('user:id,email,name')
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
