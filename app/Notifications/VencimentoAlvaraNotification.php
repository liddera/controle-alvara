<?php

namespace App\Notifications;

use App\Models\Alvara;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use Illuminate\Queue\SerializesModels;

class VencimentoAlvaraNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Alvara $alvara,
        public int $daysBefore
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusLabel = $this->daysBefore > 0 
            ? "vencerá em {$this->daysBefore} dias" 
            : "vence HOJE";

        return (new MailMessage)
            ->subject("Alerta de Vencimento: {$this->alvara->tipo}")
            ->greeting("Olá, {$notifiable->name}!")
            ->line("Este é um aviso automático de que o alvará **{$this->alvara->numero}** ({$this->alvara->tipo}) da empresa **{$this->alvara->empresa->nome}** {$statusLabel}.")
            ->line("Data de Vencimento: **{$this->alvara->data_vencimento->format('d/m/Y')}**")
            ->action('Ver Alvará no Painel', route('alvaras.show', $this->alvara))
            ->line('Recomendamos iniciar o processo de renovação o quanto antes para evitar multas ou interrupções.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'alvara_id' => $this->alvara->id,
            'empresa_nome' => $this->alvara->empresa->nome,
            'tipo' => $this->alvara->tipo,
            'numero' => $this->alvara->numero,
            'data_vencimento' => $this->alvara->data_vencimento->format('Y-m-d'),
            'days_before' => $this->daysBefore,
            'message' => "O alvará {$this->alvara->numero} vence em {$this->daysBefore} dias.",
        ];
    }
}
