# Checklist v1 — Integração WhatsApp (1 instância por cliente/tenant)

## Objetivo

Adicionar envio de **notificações** e **documentos** por WhatsApp no Alvras, com **1 instância por cliente (owner/tenant)**, usando um **gateway HTTP externo** (provedor atual: API WhatsApp via Evolution API v2), mas **sem acoplar nomes do provedor** no código (Adapter Pattern).

Este documento é um **plano ponta a ponta** em formato de checklist para guiar a implementação.

---

## Escopo do v1 (definido)

- 1 instância WhatsApp por `owner_id` (tenant).
- Tela `/profile/alerts` ganha seção “WhatsApp” com:
  - status (conectado/desconectado/precisa reconectar),
  - botão para conectar (gerar QR),
  - botão para desconectar,
  - exibição do QR (ou pairing code, se aplicável).
- Alertas automáticos (scheduler `alerts:process`) passam a enviar WhatsApp também:
  - usando a mesma regra de antecedência já existente (`alert_configs.days_before`),
  - usando uma lista de **telefones extras por alerta** (igual e-mails extras).
- Envio de documentos no modal do alvará:
  - habilitar método “WhatsApp”,
  - **não altera** o comportamento do e-mail,
  - WhatsApp envia **todos os documentos do alvará**, **1 mensagem por documento**.
- Mídia enviada por WhatsApp via **URL temporária** (padrão) e fallback **base64** (modo local/DEV quando o gateway não consegue acessar `localhost`).
- Webhooks do gateway atualizam QR e estado de conexão.

---

## Regras e princípios (arquitetura)

- Controllers leves; regras e orquestração em `Services`/`Actions`.
- Requests validam entrada; DTOs transportam dados.
- Multi-tenancy: tudo que for de WhatsApp deve estar amarrado em `owner_id`.
- Nenhum `env()` fora de `config/*.php`.
- **Não usar o nome do provedor** (ex.: “Evolution”) em classes, pastas, configs ou tabelas.
  - O código deve falar em termos de **WhatsApp Gateway** / **WhatsApp Provider**.
  - A escolha do provedor fica por configuração.

---

## Decisões de produto (travadas no v1)

- Instância por tenant: `owner_id` -> 1 instância.
- Alertas WhatsApp: enviados para `alert_configs.recipient_phones` (array).
- Envio de documento WhatsApp: 1 mensagem por documento, enviando todos.
- E-mail: continua anexando todos os documentos como hoje (sem mudanças).

---

## Endpoints do gateway (mapeamento técnico)

> Observação: abaixo está o mapeamento do **provedor atual** (Evolution API v2). No código, isso deve ficar encapsulado no adapter do provider.

### Gestão de instância

- Criar instância: `POST /instance/create`
- Conectar (QR/pairing): `GET /instance/connect/{instance}`
- Estado de conexão: `GET /instance/connectionState/{instance}`
- Reiniciar instância: `PUT /instance/restart/{instance}`
- Logout (desconectar): `DELETE /instance/logout/{instance}`
- Remover instância: `DELETE /instance/delete/{instance}`
- Listar instâncias (diagnóstico): `GET /instance/fetchInstances`

### Mensagens

- Texto: `POST /message/sendText/{instance}`
- Mídia/documento: `POST /message/sendMedia/{instance}`

### Validação de número

- Checar se é WhatsApp: `POST /chat/whatsappNumbers/{instance}`

### Webhook

- Configurar webhook: `POST /webhook/set/{instance}`
- Consultar webhook: `GET /webhook/find/{instance}`

### Settings (opcional)

- Configurar: `POST /settings/set/{instance}`
- Consultar: `GET /settings/find/{instance}`

---

## Modelagem (banco de dados)

### 1) `alert_configs`

Adicionar coluna:

- `recipient_phones` (json/array, nullable)

Regras:

- normalizar para formato E.164 quando possível (ex.: `+5599999999999`) antes de salvar;
- não permitir duplicados;
- validar quantidade e tamanho razoáveis (evitar abuso).

### 2) `whatsapp_instances` (novo)

Tabela para persistir a instância por tenant:

- `id`
- `owner_id` (index)
- `provider` (string) — ex.: `http-gateway` / `default`
- `instance_key` (string) — ex.: `owner-<owner_id>` (único)
- `status` (string) — `created|connecting|open|close|reconnect_required|error`
- `last_qr_payload` (text, nullable) — conteúdo necessário para renderizar QR (se o provedor retornar)
- `last_webhook_at` (datetime, nullable)
- `connected_at` (datetime, nullable)
- timestamps

### 3) `whatsapp_outbox` (recomendado no v1)

Fila transacional para idempotência e retry:

- `id`
- `owner_id` (index)
- `type` (string) — `alert_text|document_media`
- `to` (string) — E.164
- `payload` (json) — body a ser enviado pelo provider
- `provider_message_id` (string, nullable)
- `status` (string) — `queued|sent|failed|discarded`
- `attempts` (int)
- `last_error` (text, nullable)
- `sent_at` (datetime, nullable)
- timestamps

---

## Configuração (env/config)

Adicionar no `.env` (nomes **genéricos**, sem “Evolution”):

- `WHATSAPP_GATEWAY_BASE_URL`
- `WHATSAPP_GATEWAY_API_KEY`
- `WHATSAPP_GATEWAY_WEBHOOK_URL`
- `WHATSAPP_GATEWAY_WEBHOOK_SECRET`
- `WHATSAPP_GATEWAY_PROVIDER` (ex.: `http-v2`)
- `WHATSAPP_GATEWAY_MEDIA_MODE` (`url|base64|auto`)

Mapear em `config/services.php` sob uma chave genérica:

- `services.whatsapp_gateway.*`

---

## Componentes (código) — nomes concretos (não genéricos)

### Contratos

- `App\Contracts\WhatsApp\WhatsAppGateway`
  - `ensureInstanceForOwner(int $ownerId): WhatsAppInstanceDTO`
  - `requestConnectionQr(string $instanceKey): WhatsAppQrDTO`
  - `getConnectionState(string $instanceKey): WhatsAppConnectionStateDTO`
  - `disconnectInstance(string $instanceKey): void`
  - `sendText(string $instanceKey, string $toE164, string $text): WhatsAppSendResultDTO`
  - `sendDocumentByUrl(string $instanceKey, string $toE164, string $fileUrl, string $filename, string $mime, ?string $caption): WhatsAppSendResultDTO`
  - `sendDocumentByBase64(string $instanceKey, string $toE164, string $base64, string $filename, string $mime, ?string $caption): WhatsAppSendResultDTO`
  - `checkNumber(string $instanceKey, string $toE164): WhatsAppNumberCheckDTO`

### Adapter do provedor atual (interno)

- `App\Integrations\WhatsAppGateway\HttpV2\WhatsAppGatewayHttpV2Client`
  - Implementa `WhatsAppGateway`
  - Encapsula endpoints do provedor atual (Evolution API v2)

> Importante: o nome acima é propositalmente genérico. Não usar “Evolution” no namespace.

### Serviços/ações (regras do Alvras)

- `App\Services\WhatsApp\OwnerWhatsAppInstanceService`
  - resolve/cria instância por owner
  - salva status/QR no banco
- `App\Services\WhatsApp\WhatsAppWebhookService`
  - valida assinatura/secret
  - processa eventos e atualiza `whatsapp_instances`
- `App\Actions\Alvaras\EnviarAlvaraPorWhatsAppAction`
  - envia todos os documentos do alvará (1 msg por doc) + texto opcional
  - registra histórico em `notificacoes`
- `App\Jobs\WhatsApp\SendWhatsAppOutboxJob`
  - consome `whatsapp_outbox` e faz retry/backoff

---

## Rotas e UI

### 1) Tela `/profile/alerts` (card WhatsApp)

Local atual:

- `routes/web.php` -> rota `/profile/alerts`
- `app/Http/Controllers/AlertSettingsController.php`
- `resources/views/profile/alerts.blade.php`
- `resources/views/profile/partials/alert-settings-form.blade.php`

Checklist UI:

- [ ] Adicionar bloco “WhatsApp” (mesmo estilo do card Google)
- [ ] Botão “Conectar/Reconectar” chama backend e retorna QR
- [ ] Exibir QR no card (imagem gerada no frontend)
- [ ] Status “Conectado/Desconectado/Indisponível”
- [ ] Botão “Desconectar”
- [ ] Modal “Info” explicando limites e funcionamento

### 2) Telefones extras por alerta

Checklist UI:

- [ ] Adicionar input + chips `recipient_phones[]` (igual `recipient_emails[]`)
- [ ] Normalizar no frontend (remover espaços, `()` `-`)
- [ ] Validar no backend (request)

### 3) Modal “Enviar Documento” no alvará

Local atual:

- `resources/views/components/alvara-table.blade.php`
- endpoint: `POST /alvaras/{alvara}/enviar-email` (nome legado; manter no v1)
- controller: `app/Http/Controllers/AlvaraController.php`
- ação e-mail: `app/Actions/Alvaras/EnviarAlvaraPorEmailAction.php`

Checklist:

- [ ] Habilitar radio “WhatsApp”
- [ ] Ajustar validação no JS: exigir `email` só quando `metodo=email`; exigir `telefone` quando `metodo=whatsapp`
- [ ] Backend: ajustar validação para o mesmo comportamento
- [ ] Implementar `EnviarAlvaraPorWhatsAppAction` e chamar quando `metodo=whatsapp`
- [ ] Registrar histórico em `notificacoes` com `metodo=whatsapp`

---

## Webhooks (ponta a ponta)

### 1) Endpoint público (API)

- [ ] Criar rota em `routes/api.php` para receber webhooks do gateway
- [ ] Validar header/secret (`WHATSAPP_GATEWAY_WEBHOOK_SECRET`)
- [ ] Responder 200 rápido (processamento mínimo) e delegar para service/job

### 2) Eventos mínimos

- [ ] `QRCODE_UPDATED` -> atualizar `whatsapp_instances.last_qr_payload` e `status=connecting`
- [ ] `CONNECTION_UPDATE` -> atualizar `status=open/close` e timestamps

---

## URLs temporárias (documentos)

### Estratégia preferida

- [ ] Usar `Storage::temporaryUrl()` quando o disk suportar (S3/MinIO configurado corretamente).

### Fallback (quando não houver `temporaryUrl`)

- [ ] Criar rota assinada (signed URL) para download/stream do documento
- [ ] Link expira em poucos minutos
- [ ] Garantir que o gateway consiga acessar essa URL (rede/DNS/HTTPS)

---

## Scheduler e filas

### Alertas automáticos

Local atual:

- Scheduler: `routes/console.php` agenda `alerts:process`
- Command: `app/Console/Commands/ProcessAlvaraAlerts.php`

Checklist:

- [ ] Para cada alerta encontrado, além de `mail/database`, enfileirar envios WhatsApp (1 job por número)
- [ ] Respeitar multi-tenancy (usar `owner_id` do alvará/config)
- [ ] Não enviar se instância do owner não estiver `open`

### Retry/backoff

- [ ] Jobs com backoff e limite de tentativas
- [ ] Registrar falhas no `whatsapp_outbox.last_error`

---

## Segurança

- [ ] Webhook com secret obrigatório e validado
- [ ] Rate limit no endpoint de webhook (se necessário)
- [ ] Sanitização/normalização de telefones (evitar injeções e lixo)
- [ ] Auditoria mínima: log de eventos críticos (connect/disconnect/send fail)

---

## Testes e validação

- [ ] Teste unitário: normalização/validação E.164
- [ ] Teste feature: salvar `recipient_phones` em `alert_configs`
- [ ] Teste feature: webhook atualiza `whatsapp_instances.status`
- [ ] Teste feature: envio WhatsApp cria registros em `notificacoes` (mock do gateway)

---

## Aceite (Definition of Done)

- [ ] Admin/usuário conecta WhatsApp por QR no `/profile/alerts` e status vira “Conectado”
- [ ] Webhook atualiza estado sem refresh manual
- [ ] Alertas diários enviam WhatsApp para os telefones extras configurados
- [ ] Modal do alvará envia documentos por WhatsApp (1 msg por doc) e salva histórico
- [ ] E-mail continua funcionando exatamente como antes
