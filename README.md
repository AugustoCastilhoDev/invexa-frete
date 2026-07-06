<p align="center">
  <img src="https://raw.githubusercontent.com/AugustoCastilhoDev/invexa-frete/main/public/favicon.ico" width="80" alt="Invexa Frete">
</p>

<h1 align="center">🚛 Invexa Frete</h1>

<p align="center">
  Sistema de Gestão de Viagens da linha <strong>Invexa</strong> — controle completo de motoristas, veículos, clientes, viagens e financeiro em uma plataforma moderna e intuitiva.
</p>

<p align="center">
  <a href="https://github.com/AugustoCastilhoDev/invexa-frete/actions/workflows/tests.yml">
    <img src="https://github.com/AugustoCastilhoDev/invexa-frete/actions/workflows/tests.yml/badge.svg" alt="Tests">
  </a>
  <img src="https://img.shields.io/badge/Laravel-13.x-FF2D20?style=flat-square&logo=laravel" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.3-777BB4?style=flat-square&logo=php" alt="PHP">
  <img src="https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat-square&logo=bootstrap" alt="Bootstrap">
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql" alt="MySQL">
  <img src="https://img.shields.io/badge/Vite-5.x-646CFF?style=flat-square&logo=vite" alt="Vite">
  <img src="https://img.shields.io/badge/licença-MIT-green?style=flat-square" alt="MIT">
</p>

---

## 📦 Sobre o Projeto

O **Invexa Frete** é um sistema web completo para gestão de viagens de empresas transportadoras.
Desenvolvido em **Laravel 13 + PHP 8.3**, permite controlar todo o ciclo de uma viagem — da abertura ao acerto financeiro com o motorista — incluindo relatórios, exportação em PDF e dashboard analítico em tempo real.

> Produto da linha **Invexa** — desenvolvido por [Castilho Soluções Digitais](https://www.instagram.com/castilho_digital/)

---

## ✨ Funcionalidades

### 🗺️ Viagens
- Abertura e acompanhamento completo de viagens
- Status: Aberta → Em Andamento → Aguardando Acerto → Encerrada
- Lançamento de combustível, manutenção e outros gastos, com aprovação: lançamentos feitos pelo motorista pelo Portal ficam pendentes até um operador aprovar
- Controle de KM inicial e final
- Adiantamento ao motorista (com opção de desconto ou não)
- Descontos (vale, multa, outros)
- Documentos fiscais (CT-e, MDF-e, NF-e), com botão para verificar a autenticidade direto no portal público oficial da SEFAZ pela chave de acesso — gratuito, sem certificado digital (MDF-e pede login gov.br do próprio usuário, os demais só captcha); documento sem chave mostra um aviso, e a chave pode ser adicionada/editada depois sem precisar excluir e relançar
- Impressão de comprovante de acerto em PDF
- Rastreabilidade: cada viagem, lançamento, desconto e documento registra quem criou e quem alterou por último
- Avanço de status direto na tela da viagem, sem precisar abrir a edição; não permite pular etapas
- Assinatura digital do motorista (captura por canvas na tela da viagem), embutida no comprovante em PDF com data/hora

### 👤 Motoristas
- Cadastro completo (CPF, CNH, categoria, validade)
- Percentual de comissão padrão por motorista
- Histórico de viagens por motorista
- Busca por nome, CPF ou telefone
- CPF e CNH mascarados na tela (`123.***.***-01`), com opção de revelar o valor completo

### 🚛 Veículos
- Cadastro completo da frota (placa, modelo, marca, RENAVAM)
- Controle de status (ativo, inativo, em manutenção)
- Histórico de viagens por veículo
- Busca por placa, modelo ou marca

### 🔧 Manutenção de Veículos
- Registro de manutenção preventiva/corretiva, independente de viagem
- Data/KM da próxima manutenção prevista
- Veículo entra em status "manutenção" automaticamente ao registrar uma manutenção em andamento, e volta para "ativo" ao concluir
- Histórico e total gasto por veículo

### 🧑‍💼 Clientes
- Cadastro de Pessoa Física e Jurídica
- Busca automática de endereço por CEP (ViaCEP)
- Tabela de frete padrão por cliente
- Vinculação direta às viagens
- Busca por nome, CNPJ/CPF, cidade ou telefone
- CPF de cliente pessoa física mascarado (CNPJ não é dado pessoal, então fica visível)

### 📊 Dashboard
- Cards de resumo: viagens abertas, faturamento, lucro, frota
- Gráfico de Faturamento vs Lucro com filtros de 30, 60, 90 dias e período personalizado
- Gráfico de Viagens por Status
- Viagens em aberto
- Top 5 motoristas do mês
- Painel de **Pendências**: CNH de motorista vencida/vencendo, veículos em manutenção, documentos fiscais pendentes e manutenção preventiva vencendo

### 📈 Relatórios
- Relatório Financeiro por período com filtros avançados
- Resumo por motorista com paginação
- Composição de despesas em gráfico
- Exportação em PDF (landscape) e CSV
- Acertos por Motorista com histórico individual
- Separação de saldo a pagar vs total já pago
- **DRE simplificado** por período (Receita → Custos Diretos → Resultado Bruto → Despesas Operacionais → Resultado Líquido), considerando apenas viagens encerradas, com exportação em PDF
- **Despesas Gerais**: cadastro de custos administrativos (aluguel, salários, contas, seguro etc.) que alimentam o DRE

### 🏢 Multi-tenant (Empresas)
- Cada empresa cliente (transportadora) tem seus dados totalmente isolados dos de outra: motoristas, veículos, clientes, viagens, financeiro — tudo escopado automaticamente por empresa
- Papel **super admin**, sem empresa própria, gerencia as empresas clientes numa tela dedicada (cria a empresa e o administrador inicial dela) — não enxerga dados operacionais de nenhuma empresa
- Tela de detalhe por empresa para suporte: usuários cadastrados e resumo operacional (motoristas, veículos, clientes, viagens, despesas gerais) — sem precisar acessar o banco para investigar um chamado
- **Modo suporte**: botão "Suporte" que faz o super admin passar a navegar autenticado como o administrador daquela empresa, vendo o sistema exatamente como o cliente vê — com um aviso fixo no topo da tela e um botão para encerrar e voltar a ser super admin a qualquer momento
- Empresa pode ser desativada (ex.: inadimplência), o que bloqueia login de todos os usuários e motoristas dela imediatamente
- **Limite de veículos por plano**: cada empresa pode ter um teto de veículos configurado pelo super admin (ex.: plano até 5 veículos); tentar cadastrar acima do limite é bloqueado com uma mensagem clara, e tanto o painel de suporte quanto a própria tela de Veículos do cliente mostram "X / Y veículos" cadastrados
- E-mail, CPF, placa e CNPJ continuam únicos em todo o sistema — login por e-mail/CPF não precisa saber a qual empresa a conta pertence

### 💳 Cobrança recorrente (Asaas)
- Ao cadastrar a empresa, o super admin escolhe o plano (Starter/Pro/Business/Enterprise) e o ciclo (mensal/anual) — o sistema cria automaticamente o cliente e a assinatura recorrente no [Asaas](https://www.asaas.com/), com 14 dias de trial antes da primeira cobrança
- Plano Enterprise fica de fora da automação (sempre negociado manualmente)
- Sem `ASAAS_API_KEY` configurada, ou se a chamada à API falhar, o cadastro da empresa continua funcionando normalmente — só fica sem o vínculo de cobrança, sinalizado na tela de detalhe
- Webhook (`/webhooks/asaas`, protegido por token) registra o status de cada evento de pagamento na empresa — suspensão por inadimplência continua sendo uma decisão manual do super admin

### 👥 Usuários & Permissões
- Papéis: **super admin** (gerencia empresas clientes), **admin** (gerencia usuários da própria empresa e vê tudo dela) e **operador** (acesso operacional do dia a dia)
- Operador acessa Viagens, Motoristas, Veículos, Clientes, Lançamentos, Descontos, Documentos, Manutenções e Acertos — mas **não** vê DRE, Relatórios Financeiros nem Despesas Gerais (informação estratégica/administrativa), **não** exclui registros e **não** exclui a própria conta (só o admin apaga)
- Tela de gestão de usuários restrita a admin, escopada à própria empresa — autocadastro público desativado
- Login bloqueado para usuário inativo
- Proteções contra autodesativação e remoção do último admin ativo
- Autenticação em Dois Fatores (2FA) opcional via TOTP, com QR Code, confirmação obrigatória antes de ativar e códigos de recuperação de uso único
- Telas de login, recuperação de senha e desafio de 2FA com identidade visual própria (logo, cores da marca)
- Botão para mostrar/esconder a senha digitada no login

### 🔔 Notificações
- E-mail automático para admins ativos quando uma viagem entra em "aguardando acerto"
- E-mail automático para o motorista quando a viagem é encerrada, com o resumo do acerto
- Envio via [Resend](https://resend.com)
- Sino de notificações no topbar (para admins): contagem de não lidas e leitura por usuário — cada admin tem seu próprio estado de leitura

### ☁️ Armazenamento de arquivos
- Comprovantes de lançamento e documentos fiscais em [Cloudflare R2](https://developers.cloudflare.com/r2/) (compatível com S3), bucket privado
- Acesso somente via URL assinada e temporária (10 min) — nada fica público por padrão
- Disco configurável por variável de ambiente (`UPLOADS_DISK`): local em dev, R2 em produção, sem alteração de código

### 📱 Portal do Motorista
- Login próprio do motorista (CPF + senha), separado da autenticação de usuários do sistema
- Acesso liberado pelo admin na tela de edição do motorista, que define a senha inicial
- Motorista vê só as próprias viagens/acertos e baixa o comprovante em PDF (com a assinatura digital, se já coletada)
- Motorista lança combustível/manutenção com foto do comprovante direto da viagem — fica pendente até aprovação de um operador
- Isolamento total: um motorista não acessa dados de outro, nem o painel administrativo

### 🌐 Landing Page
- Página institucional pública na raiz (`/`) — hero, recursos, planos e preços, contato — visitante não autenticado vê a landing; quem já está logado (painel ou portal) é redirecionado direto para sua tela
- Tabela de planos com valores reais (mensal e anual) e CTA "Falar com Vendas" via WhatsApp, já com o nome do plano preenchido na mensagem

### 🔒 Segurança & LGPD
- Mascaramento de CPF/CNH na interface (comprovantes em PDF continuam completos, por serem documentos de identificação assinados)
- Política de retenção de dados configurável (`config/lgpd.php`)
- Comando `lgpd:anonimizar` que expurga dados pessoais de registros excluídos há mais tempo que o prazo configurado, preservando o histórico financeiro
- Auditoria completa: todo registro sabe quem criou e quem alterou por último
- Proteção contra força bruta: 5 tentativas de login incorretas bloqueiam novas tentativas temporariamente (por e-mail/CPF + IP), tanto no login do sistema quanto no do Portal do Motorista
- Senha com política mínima (8 caracteres) e confirmação obrigatória ao criar usuário ou empresa

### ✅ Qualidade
- 260+ testes automatizados (unitários e de feature) cobrindo cálculo financeiro, ciclo de vida de viagens, DRE, portal do motorista, permissões, 2FA, notificações, isolamento multi-tenant e anonimização de dados
- CI no GitHub Actions rodando a suíte a cada push/PR

---

## 🛠️ Tecnologias

| Camada | Tecnologia |
|--------|------------|
| Backend | Laravel 13 / PHP 8.3 |
| Frontend | Blade + Bootstrap 5.3 + Bootstrap Icons |
| Build | Vite 5.x |
| Banco de dados | MySQL 8.0 |
| Autenticação | Laravel Breeze (customizado, com papéis admin/operador) |
| 2FA | pragmarx/google2fa-laravel + bacon/bacon-qr-code |
| PDF | barryvdh/laravel-dompdf |
| E-mail | Resend (resend/resend-php) |
| Storage | Cloudflare R2 (league/flysystem-aws-s3-v3) |
| Cobrança recorrente | Asaas (API REST + webhook) |
| Internacionalização | laravel-lang/common (pt_BR) |
| Gráficos | Chart.js |
| CEP | ViaCEP API |
| Testes | PHPUnit (260+ testes) |
| CI | GitHub Actions |

---

## ⚙️ Instalação

### Pré-requisitos

- PHP >= 8.3
- Composer
- Node.js >= 18
- MySQL 8.0+

### Passo a passo

```bash
# 1. Clone o repositório
git clone https://github.com/AugustoCastilhoDev/invexa-frete.git
cd invexa-frete

# 2. Instale as dependências PHP
composer install

# 3. Copie o arquivo de ambiente
cp .env.example .env

# 4. Gere a chave da aplicação
php artisan key:generate

# 5. Configure o banco de dados no .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=invexa_frete
# DB_USERNAME=root
# DB_PASSWORD=

# 6. Execute as migrations
php artisan migrate

# 7. Instale as dependências Node e compile os assets
npm install && npm run build

# 8. Crie o link simbólico para uploads
php artisan storage:link
```

---

## 🚀 Executando em Desenvolvimento

```bash
# Terminal 1 — Servidor Laravel
php artisan serve

# Terminal 2 — Vite (hot reload)
npm run dev
```

Acesse: **http://localhost:8000**

---

## 👤 Criando a primeira empresa e usuário

O autocadastro público está desativado. As migrations já deixam um usuário **super admin** pronto (sem senha utilizável — o acesso é reivindicado pela tela "Esqueci minha senha", já que o e-mail está configurado). Com ele:

1. Faça login como super admin e acesse **Empresas**
2. Cadastre a primeira empresa cliente, informando nome e os dados do administrador inicial dela
3. A partir daí, esse admin já pode logar e criar novos usuários (admin ou operador) da própria empresa pela tela **Usuários**

Para ambiente local, se preferir pular a etapa do super admin, dá pra criar a empresa e o admin direto no banco:

```bash
php artisan tinker
```

```php
$empresa = App\Models\Empresa::create(['nome' => 'Minha Transportadora', 'status' => 'ativo']);

$admin = new App\Models\User([
    'name'     => 'Administrador',
    'email'    => 'admin@invexafrete.com',
    'password' => 'sua_senha',
    'role'     => 'admin',
    'status'   => 'ativo',
]);
$admin->empresa_id = $empresa->id;
$admin->save();
```

---

## 🚀 Deploy em Produção (VPS)

> ⚠️ Deploy em produção está pausado por decisão do time no momento. Quando for retomado, veja o checklist completo em [ROADMAP.md](ROADMAP.md), seção "Em espera".

```bash
# No servidor, dentro da pasta do projeto
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan storage:link

# Permissões
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Cron do Laravel (obrigatório)

A anonimização mensal de dados pessoais (LGPD) depende do agendador do Laravel. Adicione ao crontab do servidor:

```
* * * * * cd /caminho/do/projeto && php artisan schedule:run >> /dev/null 2>&1
```

### Variáveis de ambiente para produção

```env
APP_NAME="Invexa Frete"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seudominio.com.br
APP_TIMEZONE=America/Sao_Paulo

APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=invexa_frete
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha_segura

SESSION_DRIVER=file
CACHE_STORE=file

# E-mail (Resend) — necessário para as notificações funcionarem de verdade
MAIL_MAILER=resend
MAIL_FROM_ADDRESS="naoresponda@seudominio.com.br"
MAIL_FROM_NAME="${APP_NAME}"
RESEND_API_KEY=re_sua_chave_aqui

# Storage (Cloudflare R2) — necessário para comprovantes/documentos irem para a nuvem
UPLOADS_DISK=r2
R2_ACCESS_KEY_ID=sua_access_key
R2_SECRET_ACCESS_KEY=sua_secret_key
R2_BUCKET=seu-bucket
R2_ENDPOINT=https://SEU_ACCOUNT_ID.r2.cloudflarestorage.com

# Cobrança recorrente (Asaas) — necessário para criar assinatura ao cadastrar uma empresa nova
ASAAS_API_KEY=sua_chave_de_producao
ASAAS_ENV=production
ASAAS_WEBHOOK_TOKEN=um_token_que_voce_cadastra_tambem_no_painel_do_asaas
```

---

## 📁 Estrutura de Pastas
app/
├── Console/Commands/
│   └── AnonimizarDadosExpirados.php   # comando lgpd:anonimizar
├── Http/Controllers/
│   ├── AcertosController.php
│   ├── ClientesController.php
│   ├── DashboardController.php
│   ├── DescontosController.php
│   ├── DespesasGeraisController.php
│   ├── DocumentosController.php
│   ├── DreController.php
│   ├── EmpresasController.php          # CRUD de empresas (tenants), restrito ao super admin
│   ├── LancamentosController.php
│   ├── ManutencoesController.php
│   ├── MotoristaPortalAccessController.php  # admin libera/revoga acesso do motorista ao portal
│   ├── MotoristasController.php
│   ├── NotificacoesController.php
│   ├── RelatorioController.php
│   ├── UsersController.php
│   ├── VeiculosController.php
│   ├── ViagensController.php
│   ├── Auth/                           # login, 2FA, reset de senha (admin)
│   ├── Concerns/
│   │   └── GeraComprovanteAcerto.php   # PDF do comprovante, reaproveitado pelo admin e pelo portal
│   └── Portal/                         # controllers exclusivos do Portal do Motorista
│       ├── PortalAuthController.php
│       ├── PortalLancamentosController.php
│       ├── PortalSenhaController.php
│       └── PortalViagensController.php
├── Http/Middleware/
│   ├── EnsureUserIsAdmin.php
│   ├── EnsureUserIsSuperAdmin.php      # restringe telas de gestão de empresas ao super admin
│   └── EnsureUserIsNotSuperAdmin.php   # super admin não acessa telas operacionais (são por empresa)
├── Support/
│   └── TenantContext.php               # resolve a empresa do usuário/motorista autenticado no momento
├── Models/
│   ├── Concerns/
│   │   ├── BelongsToEmpresa.php       # escopo global + preenchimento automático de empresa_id
│   │   ├── TracksUser.php             # created_by / updated_by automáticos (guard "web")
│   │   ├── TracksDeletingUser.php     # deleted_by automático (guard "web")
│   │   └── HasUploadedFile.php        # URL do arquivo, assinada se o disco for a nuvem
│   ├── Cliente.php
│   ├── Desconto.php
│   ├── DespesaGeral.php
│   ├── Documento.php
│   ├── Empresa.php                    # tenant — empresa cliente da plataforma
│   ├── Lancamento.php
│   ├── Manutencao.php
│   ├── Motorista.php                  # Authenticatable — guard próprio do Portal
│   ├── User.php
│   ├── Veiculo.php
│   └── Viagem.php
└── Notifications/
    ├── ViagemAguardandoAcertoNotification.php
    └── ViagemEncerradaNotification.php
config/
├── auth.php                            # guards "web" (usuários) e "motorista" (portal)
└── lgpd.php                            # prazos de retenção de dados
resources/
└── views/
├── acertos/
├── clientes/
├── despesas-gerais/
├── dre/
├── empresas/                            # telas de gestão de empresas (super admin)
├── layouts/                            # app.blade.php (admin), guest.blade.php (auth), portal.blade.php
├── motoristas/
├── portal/                             # auth/, viagens/, senha/ — telas do motorista
├── relatorios/
├── users/
├── veiculos/
└── viagens/
database/
└── migrations/
tests/
├── Unit/Models/
└── Feature/
    ├── Empresas/                        # testes do CRUD de empresas
    ├── MultiTenantIsolationTest.php     # garante que uma empresa não vê dados de outra
    └── Portal/                          # testes do guard/isolamento do Portal do Motorista
routes/
├── web.php
├── auth.php                             # login/2FA/reset de senha (admin)
└── portal.php                           # login e rotas do Portal do Motorista

---

## 📄 Licença

Distribuído sob a licença MIT. Consulte o arquivo `LICENSE` para mais detalhes.

---

<p align="center">
  Desenvolvido por <strong>Augusto Castilho</strong> ·
  <a href="https://github.com/AugustoCastilhoDev">@AugustoCastilhoDev</a> ·
  <a href="https://www.instagram.com/castilho_digital/">@castilho_digital</a>
</p>

<p align="center">
  📧 Suporte: <a href="mailto:contato@invexa-app.com.br">contato@invexa-app.com.br</a>
</p>