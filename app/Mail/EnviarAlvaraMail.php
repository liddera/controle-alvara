<?php

namespace App\Mail;

use App\Models\Alvara;
use App\Models\Personalizacao;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class EnviarAlvaraMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Alvara $alvara,
        public array $dadosFormulario
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Envio de Alvará: '.($this->alvara->tipoAlvara?->nome ?? $this->alvara->tipo).' - '.$this->alvara->empresa->nome,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.alvara.enviar',
            with: [
                'alvara' => $this->alvara,
                'destinatarioNome' => $this->dadosFormulario['nome'] ?? 'Responsável',
                'mensagemPersonalizada' => $this->dadosFormulario['mensagem'] ?? null,
                'brandLogo' => $this->resolveBrandLogo(),
            ],
        );
    }

    public function attachments(): array
    {
        $attachments = [];

        foreach ($this->alvara->documentos as $documento) {
            $attachments[] = Attachment::fromStorageDisk('public', $documento->caminho)
                ->as($documento->nome_arquivo)
                ->withMime($documento->tipo);
        }

        return $attachments;
    }

    private function resolveBrandLogo(): ?array
    {
        $personalizacao = Personalizacao::query()
            ->where('owner_id', $this->alvara->owner_id)
            ->first();

        $path = $personalizacao?->header_logo_path ?: $personalizacao?->logo_path;

        if (! $path) {
            return null;
        }

        $disk = config('filesystems.default');

        if (! Storage::disk($disk)->exists($path)) {
            return null;
        }

        return [
            'contents' => Storage::disk($disk)->get($path),
            'mime' => Storage::disk($disk)->mimeType($path) ?: 'image/png',
            'name' => basename($path),
        ];
    }
}
