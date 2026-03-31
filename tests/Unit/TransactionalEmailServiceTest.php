<?php

namespace Tests\Unit;

use App\Mail\EnviarAlvaraMail;
use App\Models\Alvara;
use App\Models\DocumentDispatch;
use App\Models\DocumentDispatchMessage;
use App\Models\Empresa;
use App\Services\Email\TransactionalEmailService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TransactionalEmailServiceTest extends TestCase
{
    public function test_it_sends_email_to_provider_with_tracking_headers_and_attachments(): void
    {
        config([
            'mail.from.address' => 'envios@alvras.test',
            'mail.from.name' => 'Alvras',
            'services.email_provider.api_key' => 'provider-test-key',
            'services.email_provider.base_url' => 'https://api.email-provider.test/v3',
        ]);

        $empresa = new Empresa([
            'nome' => 'Empresa Teste',
        ]);
        $empresa->id = 77;

        $alvara = new Alvara([
            'owner_id' => 9,
            'tipo' => 'Alvara de Funcionamento',
            'numero' => 'ALV-1000/2026',
        ]);
        $alvara->id = 42;
        $alvara->setRelation('empresa', $empresa);
        $alvara->setRelation('documentos', collect([]));

        $dispatch = new DocumentDispatch([
            'owner_id' => 9,
            'alvara_id' => 42,
        ]);
        $dispatch->id = 15;

        $message = new DocumentDispatchMessage([
            'document_dispatch_id' => 15,
        ]);
        $message->id = 20;

        Http::fake([
            'https://api.email-provider.test/v3/smtp/email' => Http::response([
                'messageId' => 'provider-message-123',
            ], 201),
        ]);

        $service = new class('provider-test-key', 'https://api.email-provider.test/v3') extends TransactionalEmailService {
            protected function makeMailable(Alvara $alvara, array $dados): EnviarAlvaraMail
            {
                return new class($alvara, $dados) extends EnviarAlvaraMail {
                    public function render(): string
                    {
                        return '<html><body>Email de teste</body></html>';
                    }

                    public function subjectLine(): string
                    {
                        return 'Assunto de teste';
                    }

                    public function attachmentsForEmailProvider(): array
                    {
                        return [[
                            'name' => 'alvara.pdf',
                            'content' => base64_encode('conteudo-pdf'),
                        ]];
                    }
                };
            }
        };

        $result = $service->sendAlvaraEmail(
            alvara: $alvara,
            dados: [
                'nome' => 'Jadson Santana',
                'email' => 'jadson@example.com',
                'mensagem' => 'Segue o documento em anexo.',
            ],
            dispatch: $dispatch,
            message: $message,
        );

        $this->assertSame('provider-message-123', $result->messageId);

        Http::assertSent(function ($request) {
            $payload = $request->data();

            return $request->url() === 'https://api.email-provider.test/v3/smtp/email'
                && $request->hasHeader('api-key', 'provider-test-key')
                && ($payload['subject'] ?? null) === 'Assunto de teste'
                && ($payload['headers']['X-Mailin-custom'] ?? null) === 'dispatch_id=15;dispatch_message_id=20;owner_id=9;alvara_id=42'
                && ($payload['headers']['Idempotency-Key'] ?? null) === 'document-dispatch-message-20'
                && ($payload['attachment'][0]['name'] ?? null) === 'alvara.pdf'
                && ! empty($payload['attachment'][0]['content'] ?? null);
        });
    }
}
