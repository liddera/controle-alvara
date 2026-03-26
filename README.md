# 🚀 Alvras - Sistema de Controle de Alvarás

O **Alvras** é uma plataforma robusta desenvolvida em Laravel para gerenciar empresas e seus respectivos alvarás, focando em controle de vencimentos, notificações e armazenamento de documentos.

---

## 🛠️ Stack Tecnológica

- **Backend:** PHP 8.2+ / [Laravel 12](https://laravel.com)
- **Frontend:** Blade, TailwindCSS, Alpine.js (Breeze Starter Kit)
- **Banco de Dados:** PostgreSQL
- **Integrações:** Sanctum (API Auth), Storage (Local/S3/MinIO)
- **Assets:** Vite

---

## 📦 Módulos Implementados

### 🏢 Empresas
- CRUD completo via Web e API.
- Dashboard com estatísticas por empresa.
- Relacionamento 1:N com Alvarás.

### 📜 Alvarás
- CRUD completo com seleção de empresa.
- **Gerenciamento de Documentos:** Upload de múltiplos arquivos (PDF, PNG, JPG) com exclusão e visualização.
- **Cálculo de Status Automático:** 
  - `✔ Ativo`: Vencimento > 30 dias.
  - `⚠ Em Renovação`: Vencimento em até 30 dias.
  - `❌ Vencido`: Data de vencimento ultrapassada.

### 🔔 Notificações Automáticas
- **Configuração Customizada:** Usuários definem quantos dias de antecedência desejam ser avisados por tipo de alvará (Ex: Bombeiro 40 dias antes, Sanitário 15 dias).
- **Multicanal:** Alertas chegam via **E-mail** e através do **Sino de Notificações** no painel.
- **Interatividade:** Clique no alerta para ser redirecionado diretamente ao alvará pendente.
- **Automação Total:** Processamento diário via Scheduler e disparos assíncronos via Filas (Queues).

---

## ⚓ API (Sanctum)

As rotas da API estão protegidas e prefixadas para evitar conflitos com a Web:

- `GET /api/empresas` (Nomes de rota: `api.empresas.index`)
- `GET /api/alvaras` (Nomes de rota: `api.alvaras.index`)

**Exemplo de autenticação:**
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" http://127.0.0.1:8000/api/empresas
```

---

## 🚀 Como Rodar o Projeto

1. **Instalar Dependências:**
```bash
composer install
npm install && npm run build
```

2. **Configuração do Ambiente:**
Copie o `.env.example` para `.env` e configure seu banco PostgreSQL.

3. **Migrations e Seeders (Dados de Teste):**
```bash
php artisan migrate:fresh --seed
php artisan storage:link
```

4. **Inicie o Servidor:**
```bash
php artisan serve
```

5. **Automação de Notificações (Desenvolvimento):**
Para testar os alertas e e-mails localmente:
```bash
# Processar alertas manualmente
php artisan alerts:process

# Rodar a fila de e-mails (mantenha aberto)
php artisan queue:work

# Simular o agendador (opcional)
php artisan schedule:work
```

---

## 🔑 Acesso Padrão (Seeder)

- **Usuário:** `admin@alvras.com`
- **Senha:** `password`

---

## 📂 Estrutura de Pastas de Negócio
- `app/Http/Controllers`: Controllers Web e API.
- `app/Actions`: Lógica de negócio isolada (Ex: `CriarAlvaraAction`).
- `app/Services`: Serviços compartilhados.
- `app/DTOs`: Objetos de transferência de dados.
- `resources/views`: Templates Blade para Empresas e Alvarás.

---

## ☁️ Configuração de Armazenamento (Storage)

O sistema utiliza o padrão **Adapter Pattern** através do Laravel Filesystem/Flysystem. Isso permite trocar de provedor de arquivos (Local, MinIO, AWS S3, DigitalOcean) apenas alterando o `.env`.

### 1. Ambiente Local (MinIO)
Por padrão, o projeto está configurado para usar o **MinIO** (S3-compatible) via Docker.
- **Console:** `http://localhost:9001` (User: `minio` / Pass: `minio123`)
- **Bucket:** `alvras` (Deve ser criado manualmente no primeiro acesso ou via comando `mc`).

Para tornar os arquivos legíveis no navegador:
```bash
docker exec -it alvras-minio-1 mc alias set myminio http://localhost:9000 minio minio123 && \
docker exec -it alvras-minio-1 mc anonymous set download myminio/alvras
```

### 2. Mudando para Produção (AWS S3)
Para trocar para a **AWS S3** ou outro provedor, basta atualizar as seguintes variáveis no seu `.env`:

```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=seu_access_key
AWS_SECRET_ACCESS_KEY=seu_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=seu_bucket_producao
AWS_URL=https://seu_bucket_producao.s3.amazonaws.com
AWS_USE_PATH_STYLE_ENDPOINT=false # Use false para AWS S3 real
```

O código em `DocumentoService` e `UploadDocumentosAction` permanecerá idêntico, garantindo total portabilidade.
