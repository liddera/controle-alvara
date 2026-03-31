<?php

namespace App\Services\Email;

use App\DTOs\TransactionalEmailResultDTO;
use App\Mail\EnviarAlvaraMail;
use App\Models\Alvara;
use App\Models\DocumentDispatch;
use App\Models\DocumentDispatchMessage;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TransactionalEmailService
{
    public function __construct(
        private readonly ?string $apiKey = null,
        private readonly ?string $baseUrl = null,
    ) {}

    public function sendAlvaraEmail(
        Alvara $alvara,
        array $dados,
        DocumentDispatch $dispatch,
        DocumentDispatchMessage $message,
    ): TransactionalEmailResultDTO {
        $apiKey = $this->apiKey ?: (string) config('services.email_provider.api_key');

        if (! filled($apiKey)) {
            throw new RuntimeException('EMAIL_PROVIDER_API_KEY nao configurada.');
        }

        $mailable = $this->makeMailable($alvara, $dados);
        $htmlContent = $mailable->render();

        $payload = [
            'sender' => [
                'name' => (string) config('mail.from.name'),
                'email' => (string) config('mail.from.address'),
            ],
            'to' => [[
                'email' => (string) ($dados['email'] ?? ''),
                'name' => (string) ($dados['nome'] ?? ''),
            ]],
            'subject' => $mailable->subjectLine(),
            'htmlContent' => $htmlContent,
            'textContent' => trim(strip_tags($htmlContent)),
            'attachment' => $mailable->attachmentsForEmailProvider(),
            'headers' => [
                'X-Mailin-custom' => $this->buildTrackingHeaderValue($dispatch, $message),
                'Idempotency-Key' => $this->buildIdempotencyKey($message),
            ],
        ];

        $response = $this->request($apiKey)
            ->post('/smtp/email', $payload)
            ->throw()
            ->json();

        return new TransactionalEmailResultDTO(
            messageId: $response['messageId'] ?? null,
            raw: is_array($response) ? $response : [],
        );
    }

    public function buildTrackingHeaderValue(DocumentDispatch $dispatch, DocumentDispatchMessage $message): string
    {
        return implode(';', [
            'dispatch_id='.$dispatch->getKey(),
            'dispatch_message_id='.$message->getKey(),
            'owner_id='.$dispatch->owner_id,
            'alvara_id='.$dispatch->alvara_id,
        ]);
    }

    public function buildIdempotencyKey(DocumentDispatchMessage $message): string
    {
        return 'document-dispatch-message-'.$message->getKey();
    }

    private function request(string $apiKey): PendingRequest
    {
        $baseUrl = rtrim((string) ($this->baseUrl ?: config('services.email_provider.base_url')), '/');

        return Http::baseUrl($baseUrl)
            ->acceptJson()
            ->asJson()
            ->timeout(20)
            ->withHeaders([
                'api-key' => $apiKey,
            ]);
    }

    protected function makeMailable(Alvara $alvara, array $dados): EnviarAlvaraMail
    {
        return new EnviarAlvaraMail($alvara, $dados);
    }
}
