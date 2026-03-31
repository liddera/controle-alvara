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
            subject: $this->subjectLine(),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.alvara.enviar',
            with: $this->mailViewData(),
        );
    }

    public function attachments(): array
    {
        $attachments = [];
        $disk = config('filesystems.default');

        foreach ($this->resolveAttachmentPayloads() as $attachment) {
            $attachments[] = Attachment::fromStorageDisk($disk, $attachment['path'])
                ->as($attachment['name'])
                ->withMime($attachment['mime']);
        }

        return $attachments;
    }

    public function subjectLine(): string
    {
        return 'Envio de Alvará: '.($this->alvara->tipoAlvara?->nome ?? $this->alvara->tipo).' - '.$this->alvara->empresa->nome;
    }

    public function mailViewData(): array
    {
        $brandLogo = $this->resolveBrandLogo();

        return [
            'alvara' => $this->alvara,
            'destinatarioNome' => $this->dadosFormulario['nome'] ?? 'Responsável',
            'mensagemPersonalizada' => $this->dadosFormulario['mensagem'] ?? null,
            'brandLogo' => $brandLogo,
            'brandLogoDataUri' => $this->makeDataUri($brandLogo),
        ];
    }

    /**
     * @return array<int, array{name: string, content: string}>
     */
    public function attachmentsForEmailProvider(): array
    {
        return array_map(
            fn (array $attachment) => [
                'name' => $attachment['name'],
                'content' => base64_encode($attachment['content']),
            ],
            $this->resolveAttachmentPayloads()
        );
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

    private function makeDataUri(?array $asset): ?string
    {
        if (! $asset || empty($asset['contents']) || empty($asset['mime'])) {
            return null;
        }

        return sprintf(
            'data:%s;base64,%s',
            $asset['mime'],
            base64_encode($asset['contents'])
        );
    }

    /**
     * @return array<int, array{name: string, content: string, path: string, mime: string}>
     */
    private function resolveAttachmentPayloads(): array
    {
        $attachments = [];
        $disk = config('filesystems.default');

        foreach ($this->alvara->documentos as $documento) {
            if (! Storage::disk($disk)->exists($documento->caminho)) {
                continue;
            }

            $attachments[] = [
                'name' => $documento->nome_arquivo,
                'content' => Storage::disk($disk)->get($documento->caminho),
                'path' => $documento->caminho,
                'mime' => $documento->tipo ?: 'application/octet-stream',
            ];
        }

        return $attachments;
    }
}
