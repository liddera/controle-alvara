<?php

namespace App\Actions\Alvaras;

use App\Models\Alvara;
use App\Models\Notificacao;
use App\Jobs\Alvaras\SendAlvaraEmailAndNotifyWhatsAppJob;
use App\Services\Dispatch\DocumentDispatchService;

class EnviarAlvaraPorEmailAction
{
    public function __construct(
        private readonly DocumentDispatchService $documentDispatchService,
    ) {}

    /**
     * @param Alvara $alvara
     * @param array $dados ['nome', 'email', 'telefone', 'mensagem', 'enviar_aviso_whatsapp']
     * @return Notificacao
     */
    public function execute(Alvara $alvara, array $dados): Notificacao
    {
        $ownerId = (int) ($alvara->owner_id ?? 0);

        // Registrar histórico no model de Notificacao
        $notificacao = Notificacao::create([
            'user_id' => auth()->id() ?? $alvara->user_id,
            'alvara_id' => $alvara->id,
            'tipo' => 'envio_documento', // Chave p/ identificar e filtrar depois
            'mensagem' => json_encode([
                'destinatario_nome' => $dados['nome'] ?? 'Sem Nome',
                'destinatario_email' => $dados['email'],
                'destinatario_telefone' => $dados['telefone'] ?? null,
                'metodo' => 'email',
                'whatsapp_aviso' => (bool) ($dados['enviar_aviso_whatsapp'] ?? false),
                'mensagem_personalizada' => $dados['mensagem'] ?? null,
                'remetente_nome' => auth()->user()->name ?? 'Sistema',
            ]),
            'lida' => true,
            'data_envio' => now(),
        ]);

        [$dispatch, $dispatchMessage] = $this->documentDispatchService->createEmailDispatch(
            alvara: $alvara->loadMissing('documentos'),
            dados: $dados,
            requestedByUserId: auth()->id() ?? $alvara->user_id,
            notificacaoId: (int) $notificacao->getKey(),
        );

        SendAlvaraEmailAndNotifyWhatsAppJob::dispatch(
            alvaraId: (int) $alvara->getKey(),
            dados: $dados,
            ownerId: $ownerId,
            dispatchId: (int) $dispatch->getKey(),
            dispatchMessageId: (int) $dispatchMessage->getKey(),
        );

        $payload = json_decode($notificacao->mensagem, true) ?? [];
        $payload['dispatch_id'] = (int) $dispatch->getKey();
        $payload['dispatch_status'] = $dispatch->current_status;
        $notificacao->mensagem = json_encode($payload);
        $notificacao->save();

        return $notificacao;
    }
}
