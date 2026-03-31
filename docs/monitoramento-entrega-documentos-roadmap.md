# Roadmap de Monitoramento de Entrega de Documentos

## Objetivo

Documentar, antes da implementacao, o plano ponta a ponta para monitorar o envio de documentos do Alvras por:

- email transacional via Brevo
- WhatsApp via gateway atual baseado em Evolution API v2

O objetivo funcional e permitir que o sistema registre historico real de envio, acompanhe eventos de entrega e abertura/leitura, e apresente ao cliente um status unico e consistente por envio de documento.

Este documento consolida:

- regras de produto
- regras tecnicas
- arquitetura recomendada
- modelagem de dados
- mapeamento de status por provedor
- fases de implementacao
- checklist de execucao

## Resultado esperado para o usuario

Ao enviar um documento, o usuario deve enxergar um historico claro e progressivo, por exemplo:

- `26/03/2026 21:58 - Jadson Santana | Email | Enviando | Enviado | Recebido | Aberto`
- `26/03/2026 21:58 - Jadson Santana | WhatsApp aviso | Enviando | Enviado | Recebido | Aberto`

O sistema deve registrar os eventos reais retornados por webhook, mas exibir para o cliente um status de negocio unificado.

## Regras obrigatorias do projeto

### Skills e padroes locais

Este plano segue as regras definidas em:

- `.agent/skills/development-standard/SKILL.md`
- `.agent/skills/laravel-best-practices/SKILL.md`

Aplicacao pratica obrigatoria:

- controllers leves
- validacao em `FormRequest`
- dados de entrada trafegando por `DTO`
- regra de negocio em `Services`
- tarefas atomicas em `Actions`
- nenhum `env()` fora de `config/*.php`
- nenhuma query em Blade
- uso de `with()` para evitar N+1

### Multi-tenancy

O sistema e estritamente multi-tenant por `owner_id`.

Consequencias:

- todo envio deve estar vinculado a `owner_id`
- todo evento recebido via webhook deve ser reconciliado dentro do tenant correto
- nao pode existir correlacao de eventos entre owners diferentes

### Reaproveitamento antes de criar

Antes de criar novas estruturas, devemos reaproveitar a base que ja existe onde fizer sentido:

- `notificacoes` ja registra historico resumido de envio de documento
- `whatsapp_outbox_messages` ja modela a fila de saida do WhatsApp
- `whatsapp_instances` ja registra a conexao por tenant
- `ProcessAlvaraAlerts` ja envia alertas automaticos por email e WhatsApp

Porem, a estrutura atual nao e suficiente para rastrear eventos de ciclo de vida completos de entrega, principalmente no email.

## Diagnostico do estado atual

### O que ja existe

- envio manual de documento por email via `EnviarAlvaraPorEmailAction`
- envio manual de documento por WhatsApp via `EnviarAlvaraPorWhatsAppAction`
- job para envio efetivo do email e aviso opcional por WhatsApp
- outbox para mensagens WhatsApp
- webhook WhatsApp para estado de conexao
- historico visual basico no modal do alvara

### Limitacoes atuais

- o historico atual em `notificacoes` registra apenas o inicio do envio
- o email atual usa Laravel Mail diretamente e nao guarda `messageId` do provedor
- o webhook WhatsApp atual monitora conexao da instancia, nao entrega/leitura de mensagens
- a UI atual so mostra data, destinatario e metodo, sem progresso por etapa
- nao existe trilha de auditoria de eventos brutos por provedor

## Decisoes de produto para o v1

### Canais incluidos

- Email transacional via Brevo
- WhatsApp transacional via gateway atual

### Escopo funcional do v1

- monitorar envio manual de documentos a partir do modal do alvara
- monitorar alertas automaticos enviados pelo scheduler quando fizer sentido
- unificar historico visual por canal
- armazenar eventos brutos de webhook para auditoria
- exibir status final simplificado para o usuario

### Fora do escopo do v1

- sync retroativo de emails antigos ja enviados fora do novo fluxo
- leitura de mensagens antigas do WhatsApp para reconstruir historico
- analytics avancado por campanha
- dashboards executivos de taxa de abertura
- automacoes de reenvio automatico por comportamento do destinatario
- sincronizacao bidirecional com provedores

## Status de negocio unificado

O cliente deve ver apenas estes status:

- `enviando`
- `enviado`
- `recebido`
- `aberto`
- `falhou`
- `parcial`

### Regras do status unificado

- `enviando`: envio solicitado localmente, ainda sem confirmacao util do provedor
- `enviado`: provedor aceitou a mensagem para processamento ou envio
- `recebido`: houve confirmacao de entrega no destino
- `aberto`: houve abertura no email ou leitura no WhatsApp
- `falhou`: erro terminal de envio ou entrega
- `parcial`: envio composto por varias mensagens com resultados mistos

## Eventos por provedor

### Brevo

Eventos disponiveis confirmados para email transacional:

- `blocked`
- `click`
- `opened`
- `spam`
- `request`
- `deferred`
- `unique_opened`
- `hard_bounce`
- `soft_bounce`
- `proxy_open`
- `unsubscribed`
- `error`
- `delivered`
- `invalid_email`

### Mapeamento Brevo -> status de negocio

- `request` -> `enviado`
- `deferred` -> `enviado`
- `delivered` -> `recebido`
- `opened` -> `aberto`
- `unique_opened` -> `aberto`
- `click` -> `aberto`
- `proxy_open` -> `aberto` apenas como evento tecnico, sem prioridade acima de `opened`
- `blocked` -> `falhou`
- `hard_bounce` -> `falhou`
- `soft_bounce` -> `falhou`
- `error` -> `falhou`
- `invalid_email` -> `falhou`
- `spam` -> `falhou`
- `unsubscribed` -> registrar evento, mas nao substituir automaticamente um `recebido` ou `aberto` ja consolidado

### WhatsApp Gateway / Evolution

Eventos relevantes para v1:

- `SEND_MESSAGE`
- `MESSAGES_UPDATE`
- `CONNECTION_UPDATE`

Observacao tecnica:

- a documentacao publica e algumas issues do projeto mostram variacoes de enums e payloads em versoes recentes
- o parser deve ser tolerante a eventos adicionais, como `SEND_MESSAGE_UPDATE` e similares

### Mapeamento WhatsApp -> status de negocio

No WhatsApp, o conceito de "aberto" deve ser tratado como "lido".

- criacao local de outbox -> `enviando`
- resposta HTTP do envio com `status: PENDING` -> `enviado`
- `SEND_MESSAGE` -> `enviado`
- `MESSAGES_UPDATE` com `DELIVERY_ACK` -> `recebido`
- `MESSAGES_UPDATE` com `READ` -> `aberto`
- falha de job, rejeicao do provedor ou timeout terminal -> `falhou`

## Principio central de arquitetura

Nao usar `notificacoes` como fonte principal de monitoramento de entrega.

Motivo:

- `notificacoes` ja existe e deve ser preservada como historico resumido ou legado
- monitoramento real exige granularidade por mensagem, por evento e por provedor
- email e WhatsApp possuem modelos diferentes de confirmacao
- um envio de documento por WhatsApp pode gerar varias mensagens reais

Conclusao:

- `notificacoes` continua existindo como registro resumido
- a fonte oficial do monitoramento passa a ser uma trilha dedicada de entregas

## Modelagem recomendada

### 1) `document_dispatches`

Representa uma acao de envio percebida pelo usuario.

Campos sugeridos:

- `id`
- `owner_id`
- `alvara_id`
- `trigger_type` string: `manual|alert`
- `channel` string: `email|whatsapp|email_whatsapp_notice`
- `destination_name` nullable
- `destination_email` nullable
- `destination_phone` nullable
- `requested_by_user_id` nullable
- `requested_at`
- `current_status` string
- `status_rank` integer
- `last_event_at` nullable
- `summary_payload` json nullable
- timestamps

### 2) `document_dispatch_messages`

Representa cada mensagem real enviada ao provedor.

Campos sugeridos:

- `id`
- `document_dispatch_id`
- `owner_id`
- `provider` string: `brevo|whatsapp_gateway`
- `channel` string: `email|whatsapp`
- `message_type` string: `email_document_bundle|whatsapp_intro|whatsapp_document|whatsapp_notice`
- `documento_id` nullable
- `provider_message_id` nullable
- `provider_reference` nullable
- `provider_status_raw` nullable
- `current_status` string
- `status_rank` integer
- `destination_email` nullable
- `destination_phone` nullable
- `sent_at` nullable
- `delivered_at` nullable
- `opened_at` nullable
- `failed_at` nullable
- `metadata` json nullable
- timestamps

### 3) `document_dispatch_events`

Representa cada webhook ou evento bruto recebido.

Campos sugeridos:

- `id`
- `owner_id`
- `document_dispatch_id` nullable
- `document_dispatch_message_id` nullable
- `provider`
- `event_name`
- `event_key` nullable
- `provider_message_id` nullable
- `occurred_at` nullable
- `received_at`
- `normalized_status` nullable
- `payload` json
- timestamps

### 4) Evolucao opcional de `notificacoes`

Sem transformar `notificacoes` em trilha oficial, podemos manter uma referencia resumida:

- `mensagem` continua contendo resumo amigavel
- opcionalmente adicionar `contexto` no JSON com `document_dispatch_id`

Se isso exigir pouca friccao, ajuda a manter compatibilidade visual temporaria.

## Relacao entre envio pai e mensagens filhas

### Email

No envio de documento por email:

- 1 `document_dispatch`
- 1 `document_dispatch_message`
- varios anexos no mesmo email

### WhatsApp

No envio de documento por WhatsApp:

- 1 `document_dispatch`
- 1 mensagem inicial de texto opcional
- 1 `document_dispatch_message` por documento enviado

### Agregacao do status pai

Regra sugerida para `document_dispatch.current_status`:

- se qualquer mensagem chegar em `aberto`, o pai vira `aberto`
- senao, se qualquer mensagem chegar em `recebido`, o pai vira `recebido`
- senao, se qualquer mensagem chegar em `enviado`, o pai vira `enviado`
- se todas falharem, o pai vira `falhou`
- se houver mistura de avancos e falhas, o pai vira `parcial`

## Correlacao de eventos

### Email via Brevo

Recomendacao forte:

- substituir o envio atual baseado apenas em `Mail::to()->sendNow()` por um service proprio usando a API HTTP da Brevo

Motivos:

- a resposta da API retorna `messageId`
- a API permite usar `headers`
- o webhook da Brevo devolve `message-id` e `X-Mailin-custom`

### Cabecalhos recomendados no envio Brevo

No request de envio:

- `X-Mailin-custom: dispatch_id=<uuid>;message_id=<uuid>;owner_id=<id>;alvara_id=<id>`
- `Idempotency-Key: <uuid>`

Beneficio:

- correlacao robusta entre disparo, webhook e registros internos

### WhatsApp

A correlacao deve usar:

- `provider_message_id` vindo de `key.id`
- numero de destino
- `owner_id`
- tipo de mensagem
- `documento_id` quando aplicavel

## Configuracao e infraestrutura

### Brevo

Adicionar em `config/services.php`:

- `services.brevo.api_key`
- `services.brevo.webhook_secret` ou credencial equivalente
- `services.brevo.webhook_username` e `services.brevo.webhook_password` se for usado basic auth

Observacoes do v1:

- iniciar com webhook `batched=false`
- responder `200` rapidamente
- processar payload de forma assincrona

### WhatsApp

Ja existe base em `services.whatsapp_gateway.*`.

Ajustes do v1:

- manter nome generico do provider
- incluir eventos de mensagem no webhook configurado para a instancia
- garantir `readMessages=true` e `readStatus=true` nas settings quando aplicavel

## Componentes recomendados

### DTOs

- `DocumentDispatchRequestDTO`
- `BrevoWebhookEventDTO`
- `WhatsAppMessageEventDTO`

### Services

- `App\Services\Dispatch\DocumentDispatchService`
- `App\Services\Dispatch\DispatchStatusResolver`
- `App\Services\Dispatch\DispatchAggregationService`
- `App\Services\Email\BrevoTransactionalEmailService`
- `App\Services\Webhook\BrevoWebhookService`
- reaproveitar e evoluir `App\Services\WhatsApp\WhatsAppWebhookService`

### Actions

- `App\Actions\Alvaras\EnviarAlvaraPorEmailAction`
- `App\Actions\Alvaras\EnviarAlvaraPorWhatsAppAction`
- `App\Actions\Dispatch\RegistrarDocumentDispatchAction`
- `App\Actions\Dispatch\RegistrarDispatchEventAction`

### Jobs

- `ProcessBrevoWebhookEventJob`
- `ProcessWhatsAppWebhookEventJob`
- reaproveitar `SendWhatsAppOutboxMessageJob`
- opcionalmente criar `ReconcilePendingDispatchesJob`

### Requests

- `SendAlvaraDocumentoRequest`
- `BrevoWebhookRequest` se houver validacao dedicada
- requests atuais devem ser ajustadas para nao concentrar regra em controller

## Rotas recomendadas

### Web

Manter a rota de envio do alvara, mas evoluir o backend para um fluxo unificado.

### API publica de webhook

Adicionar:

- `POST /api/webhooks/brevo/transactional`
- reutilizar `POST /api/webhooks/whatsapp-gateway/{event?}`

Regra:

- controllers finos
- parse minimo e resposta rapida
- delegar persistencia pesada para `Service` ou `Job`

## Fluxo ponta a ponta

### 1) Envio manual por email

1. usuario abre modal do alvara
2. informa destinatario e mensagem
3. backend valida via `FormRequest`
4. action cria `document_dispatch` com status `enviando`
5. service monta payload Brevo com anexos
6. Brevo responde com `messageId`
7. sistema cria `document_dispatch_message` e atualiza status para `enviado`
8. webhook da Brevo chega com eventos posteriores
9. eventos sao gravados em `document_dispatch_events`
10. status da mensagem e do envio pai sao recalculados
11. UI mostra progresso consolidado

### 2) Envio manual por WhatsApp

1. usuario escolhe WhatsApp no modal
2. backend valida telefone e instancia conectada
3. action cria `document_dispatch` com status `enviando`
4. texto introdutorio e enfileirado no outbox quando aplicavel
5. cada documento gera uma mensagem propria
6. job envia para o gateway
7. `provider_message_id` e salvo por mensagem
8. webhook do gateway chega com eventos de entrega/leitura
9. eventos sao persistidos
10. status agregado do envio pai e recalculado
11. UI mostra progresso por canal

### 3) Alerta automatico

1. scheduler roda `alerts:process`
2. para email, criar fluxo semelhante ao envio manual quando o escopo incluir documento
3. para WhatsApp, reaproveitar outbox e trilha de monitoramento
4. `trigger_type` deve ser `alert`

## Regras de UI

### Historico do modal do alvara

Substituir o historico atual simples por um historico orientado a status.

Cada linha deve mostrar ao menos:

- data/hora da solicitacao
- destinatario
- canal
- status atual
- etapas ja concluidas

### Cores sugeridas

- `enviando`: cinza ou amarelo neutro
- `enviado`: azul
- `recebido`: verde
- `aberto`: ciano
- `falhou`: vermelho
- `parcial`: laranja

### Regras de exibicao

- `click` no email nao precisa aparecer separado; pode continuar como `aberto`
- `READ` no WhatsApp deve aparecer como `aberto` para o usuario
- eventos tecnicos como `proxy_open` devem ficar em tooltip ou detalhe tecnico, nao como status principal

## Fases de implementacao

## Fase 0 - Consolidacao tecnica

Objetivo:

- fechar naming
- travar modelagem
- confirmar contratos

Checklist:

- [ ] definir nome final das tabelas novas
- [ ] definir prioridade dos status por rank
- [ ] definir se `notificacoes` recebera referencia ao novo envio
- [ ] definir estrategia de autenticacao do webhook Brevo
- [ ] definir payload padrao de correlacao em ambos os canais

## Fase 1 - Modelagem de dados

Objetivo:

- criar a estrutura dedicada de monitoramento

Checklist:

- [ ] migration de `document_dispatches`
- [ ] migration de `document_dispatch_messages`
- [ ] migration de `document_dispatch_events`
- [ ] models e relacoes Eloquent
- [ ] scopes uteis por `owner_id`, `channel` e `current_status`
- [ ] factories basicas para testes

## Fase 2 - Camada de status unificado

Objetivo:

- centralizar a inteligencia de mapeamento

Checklist:

- [ ] criar enum ou constantes de status unificado
- [ ] criar resolver Brevo -> status unificado
- [ ] criar resolver WhatsApp -> status unificado
- [ ] criar agregador de status pai
- [ ] cobrir combinacoes com testes unitarios

## Fase 3 - Saida de email via Brevo

Objetivo:

- substituir o trecho critico que hoje nao captura `messageId`

Checklist:

- [ ] criar `BrevoTransactionalEmailService`
- [ ] mover montagem do payload para `Service`
- [ ] manter controller leve e orquestracao em action/service
- [ ] anexar documentos no formato aceito pela Brevo
- [ ] enviar `X-Mailin-custom`
- [ ] enviar `Idempotency-Key`
- [ ] salvar `provider_message_id`
- [ ] atualizar `EnviarAlvaraPorEmailAction` para usar a nova trilha
- [ ] manter compatibilidade funcional do modal

## Fase 4 - Saida de WhatsApp integrada ao monitoramento

Objetivo:

- reaproveitar o outbox existente, mas agora com rastreio oficial

Checklist:

- [ ] fazer `EnviarAlvaraPorWhatsAppAction` criar `document_dispatch`
- [ ] registrar cada mensagem filha gerada
- [ ] ligar `provider_message_id` retornado pelo gateway a `document_dispatch_messages`
- [ ] diferenciar texto introdutorio de documentos enviados
- [ ] cobrir caso de envio parcial

## Fase 5 - Webhook Brevo

Objetivo:

- sincronizar estados de email

Checklist:

- [ ] criar endpoint publico dedicado
- [ ] autenticar o webhook
- [ ] responder rapido com `200`
- [ ] persistir evento bruto
- [ ] localizar mensagem por `message-id` e `X-Mailin-custom`
- [ ] recalcular status da mensagem
- [ ] recalcular status do envio pai
- [ ] garantir idempotencia de processamento

## Fase 6 - Webhook WhatsApp

Objetivo:

- evoluir o webhook atual para monitorar mensagens

Checklist:

- [ ] incluir eventos de mensagem na configuracao da instancia
- [ ] suportar `SEND_MESSAGE`
- [ ] suportar `MESSAGES_UPDATE`
- [ ] manter suporte a `QRCODE_UPDATED` e `CONNECTION_UPDATE`
- [ ] persistir payload bruto
- [ ] localizar mensagem por `provider_message_id`
- [ ] mapear `DELIVERY_ACK` para `recebido`
- [ ] mapear `READ` para `aberto`
- [ ] tolerar eventos desconhecidos sem quebrar o processamento

## Fase 7 - UI e historico consolidado

Objetivo:

- trocar o historico visual atual por um historico real

Checklist:

- [ ] criar consulta otimizada com `with()`
- [ ] evitar qualquer query nova em Blade
- [ ] adaptar payload do modal para o novo historico
- [ ] mostrar etapas concluidas por envio
- [ ] mostrar falha e parcial de forma clara
- [ ] manter a UX simples

## Fase 8 - Scheduler e alertas automaticos

Objetivo:

- integrar a trilha de monitoramento aos fluxos automaticos

Checklist:

- [ ] revisar `ProcessAlvaraAlerts`
- [ ] decidir quais alertas automaticos entram na nova trilha no v1
- [ ] criar `document_dispatch` com `trigger_type=alert`
- [ ] manter respeito ao `owner_id`
- [ ] evitar duplicidade de disparo

## Fase 9 - Hardening e operacao

Objetivo:

- preparar para producao com observabilidade minima

Checklist:

- [ ] logs estruturados por `owner_id`, `dispatch_id` e `provider`
- [ ] idempotencia de webhook
- [ ] retry seguro de jobs
- [ ] fallback para mensagens sem correlacao
- [ ] rotina opcional de reconciliacao de pendentes
- [ ] monitoramento de falha de webhook

## Testes obrigatorios

### Unitarios

- [ ] mapper de eventos Brevo
- [ ] mapper de eventos WhatsApp
- [ ] agregador de status do envio pai

### Feature

- [ ] envio manual por email cria trilha completa
- [ ] envio manual por WhatsApp cria trilha completa
- [ ] webhook Brevo atualiza status
- [ ] webhook WhatsApp atualiza status
- [ ] historico do modal renderiza dados consolidados

### Integracao controlada

- [ ] mock de Brevo API
- [ ] mock de gateway WhatsApp
- [ ] teste de idempotencia para webhook duplicado

## Ordem recomendada de execucao

1. Fase 0
2. Fase 1
3. Fase 2
4. Fase 3
5. Fase 5
6. Fase 4
7. Fase 6
8. Fase 7
9. Fase 8
10. Fase 9

Motivo:

- email e o ponto mais bloqueante porque hoje nao existe `messageId` persistido
- a camada de status precisa nascer antes dos webhooks
- a UI deve vir depois que a trilha estiver estavel

## Definition of Done

O trabalho sera considerado concluido quando:

- todo envio manual de documento gerar um `document_dispatch`
- email via Brevo salvar `messageId` e sincronizar webhook
- WhatsApp salvar `provider_message_id` e sincronizar entrega/leitura
- o cliente visualizar status unificado por envio
- falhas e envios parciais aparecerem corretamente
- o fluxo respeitar multi-tenancy
- controllers permanecerem leves
- nao houver query em Blade
- nao houver `env()` fora de `config/*.php`

## Conclusao tecnica

O caminho recomendado para este projeto e:

- manter a UX atual do modal como ponto de entrada
- substituir o historico simplificado por uma trilha dedicada de entregas
- migrar o envio critico de email para Brevo API
- reaproveitar o outbox WhatsApp existente
- centralizar o mapeamento de eventos em uma camada de status unificado

Este roadmap deve ser a base da implementacao antes de qualquer alteracao estrutural no codigo.
