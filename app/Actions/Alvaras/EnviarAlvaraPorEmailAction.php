<?php

namespace App\Actions\Alvaras;

use App\Models\Alvara;
use App\Models\Notificacao;
use App\Mail\EnviarAlvaraMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EnviarAlvaraPorEmailAction
{
    /**
     * @param Alvara $alvara
     * @param array $dados ['nome', 'email', 'telefone', 'mensagem', 'metodo']
     * @return Notificacao
     */
    public function execute(Alvara $alvara, array $dados): Notificacao
    {
        // Disparar email
        try {
            Mail::to($dados['email'])->send(new EnviarAlvaraMail($alvara, $dados));
        } catch (\Exception $e) {
            Log::error('Erro ao enviar alvara por email: ' . $e->getMessage());
            throw new \Exception('Falha no envio do email. Verifique as configurações de servidor ou o anexo.');
        }

        // Registrar histórico no model de Notificacao
        return Notificacao::create([
            'user_id' => auth()->id() ?? $alvara->user_id,
            'alvara_id' => $alvara->id,
            'tipo' => 'envio_documento', // Chave p/ identificar e filtrar depois
            'mensagem' => json_encode([
                'destinatario_nome' => $dados['nome'] ?? 'Sem Nome',
                'destinatario_email' => $dados['email'],
                'destinatario_telefone' => $dados['telefone'] ?? null,
                'metodo' => $dados['metodo'] ?? 'email',
                'mensagem_personalizada' => $dados['mensagem'] ?? null,
                'remetente_nome' => auth()->user()->name ?? 'Sistema'
            ]),
            'lida' => true,
            'data_envio' => now(),
        ]);
    }
}
