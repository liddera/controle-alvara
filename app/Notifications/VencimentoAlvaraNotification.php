<?php

namespace App\Notifications;

use App\Models\Alvara;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\AnonymousNotifiable;
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
        if ($notifiable instanceof AnonymousNotifiable) {
            return ['mail'];
        }

        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusLabel = $this->daysBefore > 0
            ? "vencerá em {$this->daysBefore} dias"
            : 'vence HOJE';

        $recipientName = data_get($notifiable, 'name');
        $greeting = $recipientName ? "Olá, {$recipientName}!" : 'Olá!';

        return (new MailMessage)
            ->subject("Alerta de Vencimento: {$this->alvara->tipo}")
            ->greeting($greeting)
            ->line("Este é um aviso automático de que o alvará **{$this->alvara->numero}** ({$this->alvara->tipo}) da empresa **{$this->alvara->empresa->nome}** {$statusLabel}.")
            ->line("Data de Vencimento: **{$this->alvara->data_vencimento->format('d/m/Y')}**")
            ->action('Ver Alvará no Painel', route('alvaras.show', $this->alvara))
            ->line('Recomendamos iniciar o processo de renovação o quanto antes para evitar multas ou interrupções.');
    }

    public function toArray(object $notifiable): array
    {
        $statusLabel = match (true) {
            $this->daysBefore === 0 => 'vence hoje.',
            $this->daysBefore === 1 => 'vence em 1 dia.',
            default => "vence em {$this->daysBefore} dias.",
        };

        $alvaraNome = $this->alvara->tipoAlvara?->nome ?? $this->alvara->tipo;

        return [
            'alvara_id' => $this->alvara->id,
            'empresa_nome' => $this->alvara->empresa->nome,
            'tipo' => $this->alvara->tipo,
            'numero' => $this->alvara->numero,
            'data_vencimento' => $this->alvara->data_vencimento->format('Y-m-d'),
            'days_before' => $this->daysBefore,
            'message' => "O alvará {$alvaraNome}, da empresa {$this->alvara->empresa->nome}, {$statusLabel}",
        ];
    }
}
