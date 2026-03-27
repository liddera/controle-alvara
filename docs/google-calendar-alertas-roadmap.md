# Roadmap de Integracao Google Tasks para Alertas de Vencimento

## Objetivo

Documentar, antes da implementacao, o plano ponta a ponta para adicionar integracao com Google Tasks na tela de alertas/notificacoes do sistema Alvras.

Esta integracao deve permitir que o usuario conecte sua conta Google e receba como tarefa os mesmos alertas de antecedencia ja configurados no sistema para vencimento de alvaras.

## Confirmacao funcional

A referencia visual validada para esta funcionalidade e o modo `Tarefa` da interface do Google, nao o modo `Evento`.

Consequencia:

- o alvo correto da integracao e `Google Tasks`
- nao devemos modelar isso como evento comum de agenda
- o prazo da tarefa sera calculado a partir da regra de antecedencia ja existente no sistema

## Escopo do v1

- adicionar uma nova secao na tela `profile.alerts`
- exibir uma descricao curta da integracao
- exibir um botao para conectar com Google
- exibir um botao de informacao que abre um pequeno modal explicativo
- reaproveitar as regras existentes de `alert_configs`
- criar tarefas no Google com base na antecedencia ja definida pelo usuario
- salvar e reutilizar os tokens OAuth do usuario
- enviar e criar tarefas no Google sem acompanhar conclusao posterior

## Pacotes a utilizar

Sim, ainda faz sentido usar os mesmos pacotes:

- `laravel/socialite`
- `google/apiclient`

### Papel de cada pacote

#### `laravel/socialite`

Usado para:

- redirecionar o usuario para o login/autorizacao Google
- receber os dados basicos do retorno OAuth
- simplificar o fluxo de autenticacao social no Laravel

#### `google/apiclient`

Usado para:

- consumir a Google Tasks API depois que o usuario estiver autenticado
- criar tarefas
- renovar access token com refresh token

### Conclusao tecnica

O fluxo ideal para este projeto e:

- `Socialite` para OAuth inicial
- `google/apiclient` para operacoes da Google Tasks API

## Regras obrigatorias do projeto

### Padrao de arquitetura

Seguir os guias locais:

- `.agent/skills/development-standard/SKILL.md`
- `.agent/skills/laravel-best-practices/SKILL.md`

Aplicacao pratica:

- controllers devem permanecer leves
- requests devem validar entrada
- DTOs devem transportar dados de entrada
- services devem concentrar regra de negocio e orquestracao
- actions devem executar tarefas atomicas
- nenhuma query em Blade
- nenhum `env()` fora de `config/*.php`

### Multi-tenancy

- o sistema e multi-tenant por `owner_id`
- qualquer model novo ou evoluido que pertença ao tenant deve respeitar `owner_id`
- nenhum envio de tarefa pode cruzar dados entre owners

### Reaproveitamento antes de criar

Antes de criar qualquer tabela, coluna ou fluxo novo, deve-se reaproveitar o que ja existe:

- `alert_configs` ja contem a regra de antecedencia
- `users` ja contem campos Google parciais

Nao criar estrutura duplicada se a atual puder ser evoluida com seguranca.

## Diagnostico do estado atual

### O que ja existe

#### Tela de alertas

- rota: `/profile/alerts`
- controller: `App\Http\Controllers\AlertSettingsController`
- view principal: `resources/views/profile/alerts.blade.php`
- partial principal: `resources/views/profile/partials/alert-settings-form.blade.php`

Hoje a tela ja permite:

- cadastrar `days_before`
- opcionalmente filtrar por `tipo_alvara_id`
- adicionar emails extras para receber notificacao

#### Regras de alerta

A tabela `alert_configs` ja representa a regra principal de negocio:

- owner
- usuario
- tipo de alvara opcional
- quantidade de dias antes
- status ativo
- emails adicionais

Essa tabela deve continuar sendo a fonte unica da verdade para antecedencia.

#### Tokens Google

Na tabela `users` ja existem:

- `google_id`
- `google_token`
- `google_refresh_token`

Portanto:

- nao devemos criar `google_access_token` novo
- nao devemos recriar `google_refresh_token`

#### Estrutura antiga de calendario

Existe uma base chamada `calendar_events`, mas ela esta orientada a evento, com nomes como:

- `google_event_id`
- `tipo_evento`
- `data_evento`

Como agora o requisito correto e `Google Tasks`, essa estrutura nao sera usada no v1 se a estrategia for apenas criar a tarefa e encerrar o fluxo sem sincronizacao posterior.

## Decisoes de produto para o v1

### Fonte da antecedencia

A antecedencia sera sempre a configurada em `alert_configs`.

Consequencia:

- nao havera uma segunda regra especifica para Google Tasks no v1
- quando o usuario configurar `15 dias antes`, esta mesma regra sera usada para email, painel e tarefa Google

### Lista padrao de tarefas

No v1, a lista de destino sera a lista padrao do usuario.

Consequencia:

- nao havera seletor de lista no primeiro release
- se no futuro precisarmos suportar multiplas listas, a estrutura ja deve prever isso

### Data da tarefa

No v1, o prazo da tarefa sera a data calculada pela antecedencia.

Exemplo:

- vencimento real do alvara: `30/06/2026`
- regra: `15 dias antes`
- prazo da tarefa Google: `15/06/2026`

O vencimento real do alvara continuara visivel na descricao da tarefa.

### Escopo inicial

No v1 nao entra:

- escolha manual de task list pelo usuario
- sync bidirecional
- importacao de tarefas do Google para dentro do sistema
- regras separadas para Google
- sincronizacao retroativa de historico vencido
- tarefas com subtarefas ou checklist interno
- atualizacao posterior de tarefa ja criada
- remocao automatica de tarefa ja criada
- leitura do status de concluida no Google

## Modelagem recomendada

### Tabela `users`

Reaproveitar campos existentes e adicionar apenas o necessario.

#### Manter

- `google_id`
- `google_token`
- `google_refresh_token`

#### Adicionar

- `google_token_expires_at` nullable
- `google_tasklist_id` nullable com default `@default`
- `google_tasklist_title` nullable

#### Ajustar

- avaliar migracao de `google_token` para `text`
- avaliar migracao de `google_refresh_token` para `text`

Motivo:

- tokens podem crescer
- evita limitacao desnecessaria de tamanho
- a lista padrao pode ser persistida de forma explicita

### Persistencia local no v1

No v1, nao e obrigatorio manter uma tabela local para acompanhar cada tarefa criada no Google.

Motivo:

- o requisito aprovado e apenas enviar e criar a tarefa
- nao precisamos acompanhar conclusao posterior
- nao precisamos atualizar a tarefa depois
- nao precisamos remover a tarefa depois

Consequencia:

- o sistema faz o envio
- a tarefa passa a ser gerenciada pelo proprio usuario no Google
- o nosso banco guarda apenas o necessario para autenticacao Google

## Estrutura tecnica recomendada

## Camadas

### Controllers

Criar controller especifico para OAuth Google:

- `GoogleTasksController`

Responsabilidades:

- redirecionar usuario para autorizacao Google
- receber callback
- desconectar integracao

Sem regra pesada de negocio dentro do controller.

### Requests

Criar requests apenas se houver entrada adicional a validar.

No fluxo OAuth puro, a maior parte da validacao vira do callback e do estado da sessao.

### DTOs

Criar DTOs se necessario para:

- payload de conexao
- payload de sincronizacao
- payload de criacao de tarefa

### Services

Criar services dedicados:

- `GoogleTasksService`
- `GoogleOAuthService` ou concentrar no service principal

Responsabilidades:

- criar client Google
- renovar token
- descobrir task list padrao
- criar tarefas

### Actions

Criar actions atomicas, por exemplo:

- `ConnectGoogleTasksAction`
- `HandleGoogleTasksCallbackAction`
- `CreateGoogleTaskAction`

### Jobs

Para operacoes custosas ou em lote:

- `CreateGoogleTaskJob`

## Fluxo funcional ponta a ponta

### Fase 1 - Preparacao do Google Cloud

#### Objetivo

Criar e preparar as credenciais OAuth.

#### Checklist

- criar projeto no Google Cloud
- habilitar Google Tasks API
- configurar OAuth consent screen
- criar OAuth Client do tipo Web Application
- cadastrar redirect URI de desenvolvimento
- guardar `client_id` e `client_secret`

#### Parametros iniciais sugeridos

- origem JS dev: `http://localhost:8000`
- redirect URI dev: `http://localhost:8000/google/callback`

### Fase 2 - Configuracao da aplicacao Laravel

#### Objetivo

Preparar dependencias e configuracoes seguras.

#### Checklist

- instalar `laravel/socialite`
- instalar `google/apiclient`
- adicionar config `google` em `config/services.php`
- configurar variaveis no `.env`

#### Variaveis esperadas

- `GOOGLE_CLIENT_ID`
- `GOOGLE_CLIENT_SECRET`
- `GOOGLE_REDIRECT_URI`

#### Regra

Nao usar `env()` diretamente em classes da aplicacao.

### Fase 3 - Evolucao do banco de dados

#### Objetivo

Ajustar somente o que falta.

#### Checklist

- criar migration para completar `users`
- adicionar indices necessarios
- revisar casts e fillable dos models

#### Regra

Nao duplicar colunas ja existentes na tabela `users`.

### Fase 4 - Implementacao do fluxo OAuth

#### Objetivo

Permitir conectar e desconectar a conta Google.

#### Rotas sugeridas

- `GET /google/redirect`
- `GET /google/callback`
- `DELETE /google/disconnect`

#### Regras

- usar Socialite para a etapa de autorizacao
- solicitar escopo minimo necessario para Google Tasks
- solicitar acesso offline para refresh token
- salvar os dados retornados no usuario autenticado
- manter a integracao vinculada ao usuario logado

#### Dados persistidos no callback

- `google_id`
- `google_token`
- `google_refresh_token`
- `google_token_expires_at`
- `google_tasklist_id`
- `google_tasklist_title`

### Fase 5 - Atualizacao da interface

#### Objetivo

Adicionar a nova secao de Google Tarefas na tela de alertas.

#### Elementos da secao

- titulo da integracao
- descricao curta
- botao `Conectar com Google`
- botao `Info`
- modal pequeno com explicacao

#### Texto base sugerido

- descricao curta:
  - `Conecte ao Google para receber alertas tambem como tarefas.`
- resumo do modal:
  - a integracao cria tarefas no Google usando as regras de antecedencia que voce ja configurou no sistema
  - o prazo da tarefa sera calculado com base na antecedencia do alerta
  - o vencimento real do alvara aparecera na descricao da tarefa
  - apenas itens futuros serao sincronizados

#### Estados visuais

- nao conectado
- conectado
- erro de sincronizacao
- reconexao recomendada

### Fase 6 - Momento de criacao da tarefa

#### Objetivo

Definir quando a tarefa sera enviada ao Google.

#### Regra aprovada

- usar `data_vencimento` do alvara como base
- usar `days_before` da configuracao existente
- calcular o prazo da tarefa:
  - `prazo_tarefa = data_vencimento - days_before`
- criar a tarefa no Google com esse prazo
- incluir o vencimento real na descricao
- depois de criada, nao precisamos acompanhar mudancas futuras no Google

#### Resultado esperado

O usuario passa a receber a tarefa no Google e depois pode gerencia-la por la, inclusive marcar como concluida, sem retorno para o nosso banco.

### Fase 7 - Tratamento de falhas

#### Objetivo

Dar previsibilidade quando o Google falhar no momento do envio.

#### Regra operacional

O fluxo de email e painel continua existindo e nao depende do Google.

#### Regras

- nao quebrar o fluxo principal do sistema
- manter alertas por email e painel funcionando mesmo sem Google
- orientar reconexao quando token estiver invalido

### Fase 8 - Desconexao

#### Objetivo

Permitir remover a integracao com seguranca.

#### Comportamento recomendado para o v1

- limpar tokens do usuario
- limpar ou resetar `google_tasklist_id`
- interromper novos envios futuros
- manter as tarefas ja criadas no Google sob controle do usuario

### Fase 9 - Testes

#### Objetivo

Cobrir o fluxo critico com testes automatizados.

#### Testes minimos

- tela de alertas mostra secao Google Tarefas
- botao de conexao redireciona corretamente
- callback salva dados da conta
- criacao de tarefa envia payload correto para o Google
- calculo de prazo usa `data_vencimento - days_before`
- tenancy impede cruzamento entre owners

## Regras de negocio detalhadas

### Regra 1 - Fonte unica de antecedencia

`alert_configs` e a unica fonte de configuracao de antecedencia.

### Regra 2 - Email, painel e tarefa usam a mesma regra

Se o usuario configurou `X dias antes`, esta mesma antecedencia vale para:

- email
- notificacao interna
- tarefa Google

### Regra 3 - Sem configuracao Google separada no v1

Nao criar tabela nova apenas para "preferencia de antecedencia Google".

### Regra 4 - Escopo por owner

Toda operacao deve respeitar `owner_id`.

### Regra 5 - Sem retorno de concluida

Depois que a tarefa for criada no Google, o usuario pode marcar como concluida por la e o nosso sistema nao precisa receber esse retorno.

### Regra 6 - Sem atualizacao posterior obrigatoria

No v1, nao vamos atualizar tarefas ja criadas quando houver mudanca posterior no alvara ou no alerta.

### Regra 7 - Resiliencia

Falha no Google nao pode impedir:

- cadastro e edicao de alvaras
- processamento de email
- notificacoes internas

## Estrutura de dados recomendada para a tarefa Google

### Titulo

Formato sugerido:

- `Renovar alvara: {tipo do alvara} - {empresa}`

### Descricao

Conter:

- empresa
- tipo do alvara
- numero do alvara
- data real de vencimento
- antecedencia aplicada
- link para o alvara no sistema

### Prazo

No v1, o `due` da tarefa sera a data calculada pela antecedencia.

Formula:

- `due = data_vencimento - days_before`

### Lista

No v1, usar a lista padrao do usuario.

## Riscos e pontos de atencao

### Tokens

- refresh token pode nao vir em todas as reconexoes
- token pode expirar ou ser revogado
- app em modo de teste no Google pode impor restricoes

### Dados

- como nao havera atualizacao posterior no v1, se a data mudar depois da criacao a tarefa antiga no Google continuara como foi criada

### Produto

- tarefa no Google Tasks nao e igual a evento de agenda
- se o usuario esperar bloco visual no calendario, isso nao sera entregue pelo v1

### Operacao

- criar muitas tarefas em lote exige fila ou processamento cuidadoso

## Ordem de implementacao recomendada

1. preparar configuracao Google Cloud e `.env`
2. instalar dependencias
3. criar migrations minimas
4. ajustar models
5. implementar OAuth
6. adicionar secao e modal na tela
7. implementar service/action de criacao de tarefa
8. cobrir com testes
9. validar rollout em ambiente de desenvolvimento

## Checklist final de pronto para iniciar

- documentacao aprovada
- decisoes de modelagem aprovadas
- escopo do v1 fechado
- credenciais Google disponiveis
- rotas e naming definidos
- migrations planejadas sem duplicacao
- estrategia de testes definida

## Arquivos provavelmente envolvidos na implementacao

- `config/services.php`
- `routes/web.php`
- `app/Http/Controllers/AlertSettingsController.php`
- `app/Http/Controllers/GoogleTasksController.php`
- `app/Services/GoogleTasksService.php`
- `app/Services/AlertConfigService.php`
- `app/Actions/Alerts/*`
- `app/Models/User.php`
- `resources/views/profile/alerts.blade.php`
- `resources/views/profile/partials/alert-settings-form.blade.php`
- `database/migrations/*`
- `tests/Feature/AlertSettingsTest.php`

## Referencias oficiais

- Laravel Socialite: https://laravel.com/docs/12.x/socialite
- Google Tasks API overview: https://developers.google.com/workspace/tasks/overview
- Google Tasks API auth/scopes: https://developers.google.com/workspace/tasks/auth
- Google Tasks REST resource: https://developers.google.com/tasks/reference/rest/v1/tasks
- Google OAuth for web server apps: https://developers.google.com/identity/protocols/oauth2/web-server

## Observacao final

Este documento define o plano de implementacao antes do inicio do codigo. A execucao deve seguir este roadmap, preservando o padrao arquitetural do projeto e evitando criar estruturas redundantes onde o sistema ja possui base pronta.
