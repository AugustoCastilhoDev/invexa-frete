<p align="center">
  <img src="https://raw.githubusercontent.com/AugustoCastilhoDev/invexa-frete/main/public/favicon.png" width="80" alt="Invexa Frete">
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
- Lançamentos de combustível registram KM do veículo e litros abastecidos, usados para calcular a **média de combustível (km/L)** da viagem
- Controle de KM inicial e final
- Adiantamento ao motorista (com opção de desconto ou não)
- Descontos (vale, multa, adiantamento, outros) e **Bonificação** (diária, prêmio — soma ao saldo do motorista em vez de subtrair)
- Documentos fiscais (CT-e, MDF-e, NF-e), com botão para verificar a autenticidade direto no portal público oficial da SEFAZ pela chave de acesso — gratuito, sem certificado digital (MDF-e pede login gov.br do próprio usuário, os demais só captcha); documento sem chave mostra um aviso, e a chave pode ser adicionada/editada depois sem precisar excluir e relançar
- Upload de comprovante (jpg/png/pdf) direto no formulário de lançamento na tela da viagem, tanto pelo motorista (Portal) quanto pelo operador
- Impressão de comprovante de acerto em PDF
- Rastreabilidade: cada viagem, lançamento, desconto e documento registra quem criou e quem alterou por último
- Avanço de status direto na tela da viagem, sem precisar abrir a edição; não permite pular etapas
- Assinatura digital do motorista (captura por canvas na tela da viagem), embutida no comprovante em PDF com data/hora
- **Programação de Frota**: planeje o motorista/veículo/cliente (e opcionalmente o valor do frete, se já negociado) da próxima viagem antes de encerrar a atual, numa tela dedicada com visão de quais veículos já têm plano definido e quais estão livres; a confirmação abre a viagem de verdade a partir dos dados programados, sem duplicar cadastro
- **Controle de recebimento do frete**: na listagem de viagens, confirmação com um clique de que o frete foi recebido do cliente (com data), filtro por recebido/pendente e exportação em CSV — o recebimento já entra no faturamento do Dashboard e no Relatório Financeiro mesmo antes da viagem encerrar, sem contar em duplicidade quando o acerto fecha depois

### 👤 Motoristas
- Cadastro completo (CPF, CNH, categoria, validade)
- Percentual de comissão padrão por motorista
- Histórico de viagens por motorista
- Busca por nome, CPF ou telefone
- CPF e CNH mascarados na tela (`123.***.***-01`), com opção de revelar o valor completo
- Importação em massa via CSV, com modelo para baixar e relatório de linhas com erro

### 🚛 Veículos
- Cadastro completo da frota (placa, modelo, marca, RENAVAM)
- Controle de status (ativo, inativo, em manutenção)
- Histórico de viagens por veículo
- Busca por placa, modelo ou marca
- Importação em massa via CSV, respeitando o limite de veículos do plano

### 🔧 Manutenção de Veículos
- Registro de manutenção preventiva/corretiva, independente de viagem
- Data/KM da próxima manutenção prevista
- Veículo entra em status "manutenção" automaticamente ao registrar uma manutenção em andamento, e volta para "ativo" ao concluir
- Histórico e total gasto por veículo
- Histórico consolidado da frota inteira (`/manutencoes`), sem precisar abrir veículo por veículo — filtros, paginação e exportação CSV

### 🧑‍💼 Clientes
- Cadastro de Pessoa Física e Jurídica
- Busca automática de endereço por CEP (ViaCEP)
- Tabela de frete padrão por cliente
- Vinculação direta às viagens
- Busca por nome, CNPJ/CPF, cidade ou telefone
- CPF de cliente pessoa física mascarado (CNPJ não é dado pessoal, então fica visível)
- Importação em massa via CSV

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
- Média de combustível (km/L) do período, a partir do total de KM rodado e litros abastecidos
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

### 🩺 Diagnóstico do Sistema (super admin)
- Tela `/diagnostico`, restrita ao super admin: saúde do servidor (uptime, carga de CPU, memória e disco usados) e saúde da aplicação (usuários online, empresas ativas, tamanho do banco, volume de veículos/motoristas/clientes/viagens de todas as empresas)
- Métricas de servidor lidas nativamente pelo PHP (`/proc`, `sys_getloadavg()`, `disk_free_space()`), sem depender de `shell_exec`

### 💳 Cobrança recorrente (Asaas)
- Ao cadastrar a empresa, o super admin escolhe o plano (Starter/Pro/Business/Enterprise) e o ciclo (mensal/anual) — o sistema cria automaticamente o cliente e a assinatura recorrente no [Asaas](https://www.asaas.com/), com 14 dias de trial antes da primeira cobrança
- Plano Enterprise fica de fora da automação (sempre negociado manualmente)
- Sem `ASAAS_API_KEY` configurada, ou se a chamada à API falhar, o cadastro da empresa continua funcionando normalmente — só fica sem o vínculo de cobrança, sinalizado na tela de detalhe
- Webhook (`/webhooks/asaas`, protegido por token) registra o status de cada evento de pagamento na empresa — suspensão por inadimplência continua sendo uma decisão manual do super admin
- Badge de situação de cobrança (Em dia / Atrasado / Em trial / Sem assinatura / Reembolsado) na listagem de empresas, clicável, levando direto à seção de Cobrança de cada uma

### 📄 Emissão de CT-e/MDF-e (Focus NFe)
- Estrutura completa de emissão real de CT-e/MDF-e via [Focus NFe](https://focusnfe.com.br/), mas **desligada por padrão** — fica inerte para toda empresa até um super admin ativar manualmente para uma cliente específica (nenhum plano da Focus é contratado até haver demanda real; ver ROADMAP.md para o contexto)
- Ativação em `/empresas/{id}`: upload do certificado digital A1 (.pfx) + senha, escolha de ambiente (homologação/produção) — token retornado pela Focus é guardado criptografado
- Card "Dados Fiscais" na mesma tela (endereço completo com busca por CEP, IE, RNTRC, regime tributário, CFOP, ICMS) alimenta o payload real de emissão — campos ficam vazios até serem confirmados com o contador da transportadora, sem valor chutado
- **Cargas**: uma viagem pode atender vários clientes/destinatários na mesma rota — cada "Carga" agrupa as NF-e's de um cliente e vira a unidade de emissão do CT-e (destinatário e valor do frete próprios); ao autorizar, o XML e o DACTE/DAMDFE são baixados dos servidores da Focus e guardados no nosso próprio storage, disponíveis para download junto com os documentos lançados manualmente
- **Encerramento de MDF-e**: botão dedicado para encerrar o manifesto na SEFAZ ao fim da viagem — abrir uma nova viagem para um veículo com MDF-e ainda não encerrado é bloqueado; o MDF-e referencia automaticamente todos os CT-e's autorizados de todas as cargas da viagem
- **Unidades (matriz/filial)**: uma empresa pode cadastrar filiais com CNPJ/IE/endereço fiscal próprios (mesma raiz de CNPJ, sufixo de ordem diferente); ao criar a viagem/carga dá pra escolher qual unidade emite aquele CT-e/MDF-e — frota, usuários e limite de veículos continuam compartilhados, sem duplicar tenant
- **Telas separadas por tipo** — `/emissoes-fiscais/cte` e `/emissoes-fiscais/mdfe` (dois itens no menu, "CT-e" e "MDF-e"), cada uma só com os filtros/colunas que fazem sentido pra ela (Cliente e Carga só aparecem no CT-e; Encerrado em e o botão de encerrar só no MDF-e), paginação e exportação CSV própria por tipo
- Webhook (`/webhooks/focus-nfe`, protegido por token) atualiza o status de cada emissão — nunca desativa a empresa nem toma nenhuma ação automática sozinho

### 👥 Usuários & Permissões
- Papéis: **super admin** (gerencia empresas clientes), **admin** (gerencia usuários da própria empresa e vê tudo dela) e **operador** (acesso operacional do dia a dia)
- Operador acessa Viagens, Motoristas, Veículos, Clientes, Lançamentos, Descontos, Documentos, Manutenções e Acertos — mas **não** vê DRE, Relatórios Financeiros nem Despesas Gerais (informação estratégica/administrativa), **não** exclui registros e **não** exclui a própria conta (só o admin apaga)
- Tela de gestão de usuários restrita a admin, escopada à própria empresa — autocadastro público desativado
- Login bloqueado para usuário inativo
- Proteções contra autodesativação e remoção do último admin ativo
- Autenticação em Dois Fatores (2FA) opcional via TOTP, com QR Code, confirmação obrigatória antes de ativar e códigos de recuperação de uso único
- Telas de login, recuperação de senha e desafio de 2FA com identidade visual própria (logo, cores da marca)
- Botão para mostrar/esconder a senha digitada no login
- Alternância entre o login de Operador/Admin e o Portal do Motorista direto na tela, nos dois sentidos

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
- Motorista lança combustível/manutenção com foto do comprovante direto da viagem, informando também KM do veículo e litros abastecidos quando for combustível — fica pendente até aprovação de um operador
- Isolamento total: um motorista não acessa dados de outro, nem o painel administrativo

### 🌐 Landing Page
- Página institucional pública na raiz (`/`) — hero, recursos, planos e preços, contato — visitante não autenticado vê a landing; quem já está logado (painel ou portal) é redirecionado direto para sua tela
- Demonstração interativa em abas (Dashboard, Viagens, Acerto, Portal do Motorista): mockups ilustrativos em HTML/CSS com os dados e cores reais do sistema, sem depender de screenshots ou de dados de clientes
- Acesso direto ao Portal do Motorista e ao login do painel no cabeçalho, ao lado do CTA "Falar com Vendas"
- Tabela de planos com valores reais (mensal e anual) e CTA "Falar com Vendas" via WhatsApp, já com o nome do plano preenchido na mensagem
- Grade de recursos com 8 cards, incluindo Programação de Frota e Controle de Recebimento; selo de confiança "Backup diário automático" ao lado de teste grátis, isolamento por empresa e conformidade com a LGPD

### 🌓 Painel responsivo e personalizável
- Modo claro/escuro (alternância pelo card de usuário no sidebar), preferência salva no navegador
- Relógio/data ao vivo no sidebar
- Sidebar off-canvas e tabelas com rolagem horizontal em telas de celular (abaixo de 992px)
- Menu lateral agrupado por uso: **Principal** (Dashboard, Viagens, Programação de Frota, Acertos) e **Cadastros**/**Fiscal** ficam visíveis a todo usuário; itens restritos a admin (Financeiro, DRE, Despesas Gerais, Usuários) ficam consolidados num único bloco **Administração**, mais abaixo — quem é operador não vê nenhum item que não pode acessar

### 🔒 Segurança & LGPD
- Mascaramento de CPF/CNH na interface (comprovantes em PDF continuam completos, por serem documentos de identificação assinados)
- Política de retenção de dados configurável (`config/lgpd.php`)
- Comando `lgpd:anonimizar` que expurga dados pessoais de registros excluídos há mais tempo que o prazo configurado, preservando o histórico financeiro
- **Log de acesso à aplicação** (Marco Civil da Internet, Art. 15): todo login (painel e Portal do Motorista) grava IP e data/hora; comando `lgpd:expurgar-logs-acesso` apaga os registros com mais de 12 meses
- Auditoria completa: todo registro sabe quem criou e quem alterou por último
- Proteção contra força bruta: 5 tentativas incorretas bloqueiam novas tentativas temporariamente (por e-mail/CPF + IP) no login do sistema, no do Portal do Motorista e também no desafio de código 2FA (por usuário)
- Senha com política mínima (8 caracteres) e confirmação obrigatória ao criar usuário ou empresa
- **Backup automatizado** diário do banco de dados (+ uploads locais), criptografado, com cópia local e outra fora do servidor (Cloudflare R2); limpeza de backups antigos e monitoramento com alerta por e-mail se algo ficar velho ou quebrado (`spatie/laravel-backup`, ver `config/backup.php`)
- **Termos de Uso** e **Política de Privacidade** públicos, linkados no rodapé de todas as telas (landing, painel, portal e login)

### ✅ Qualidade
- 440+ testes automatizados (unitários e de feature) cobrindo cálculo financeiro, ciclo de vida de viagens, DRE, portal do motorista, permissões, 2FA, notificações, isolamento multi-tenant, anonimização de dados, log de acesso, emissão/encerramento de CT-e/MDF-e e diagnóstico do sistema
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
| Emissão fiscal | Focus NFe API (CT-e/MDF-e) |
| Municípios/UF | API pública do IBGE |
| Testes | PHPUnit (440+ testes) |
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

> ✅ Em produção desde 2026-07-07 em [invexafrete.com.br](https://invexafrete.com.br) (VPS Hostinger, Nginx + PHP-FPM 8.3 + MySQL, deploy manual via SSH). O passo a passo abaixo é o guia genérico usado nesse deploy — veja [ROADMAP.md](ROADMAP.md), seção "Deploy em produção", para o resumo do que está configurado.

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

A anonimização mensal de dados pessoais (LGPD) e o **backup diário automatizado** dependem do agendador do
Laravel. Adicione ao crontab do servidor (se já estiver configurado, nenhuma ação extra é necessária — o backup
usa o mesmo agendador):

```
* * * * * cd /caminho/do/projeto && php artisan schedule:run >> /dev/null 2>&1
```

O backup do banco de dados depende do binário `mysqldump` estar disponível no servidor. Confirme com:

```
which mysqldump
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

# Emissão de CT-e/MDF-e (Focus NFe) — opcional; sem isso, a ativação por
# empresa em /empresas/{id} simplesmente falha com aviso no log, sem travar
# o resto do sistema. Só preencher quando um plano for realmente contratado.
FOCUS_NFE_TOKEN_CONTA_PRINCIPAL=
FOCUS_NFE_WEBHOOK_TOKEN=

# Backup automático — sem a senha, o backup roda sem criptografia (não recomendado)
BACKUP_ARCHIVE_PASSWORD=uma_senha_forte_so_para_os_backups
BACKUP_NOTIFICATION_EMAIL=contato@invexa-app.com.br
```

---

## 📁 Estrutura de Pastas
app/
├── Console/Commands/
│   ├── AnonimizarDadosExpirados.php   # comando lgpd:anonimizar
│   └── ExpurgarLogsAcesso.php         # comando lgpd:expurgar-logs-acesso
├── Http/Controllers/
│   ├── AcertosController.php
│   ├── CargasController.php            # agrupa NF-e por cliente dentro de uma viagem (unidade de emissão do CT-e)
│   ├── ClientesController.php
│   ├── DashboardController.php
│   ├── DescontosController.php
│   ├── DespesasGeraisController.php
│   ├── DiagnosticoController.php       # saúde do servidor + aplicação, restrito ao super admin
│   ├── DocumentosController.php
│   ├── DreController.php
│   ├── EmissoesFiscaisController.php   # telas /emissoes-fiscais/cte e /mdfe + emissão/encerramento via Focus NFe
│   ├── EmpresasController.php          # CRUD de empresas (tenants), restrito ao super admin
│   ├── LancamentosController.php
│   ├── ManutencoesController.php
│   ├── MotoristaPortalAccessController.php  # admin libera/revoga acesso do motorista ao portal
│   ├── MotoristasController.php
│   ├── NotificacoesController.php
│   ├── ProfileController.php
│   ├── ProgramacoesViagemController.php # programação de frota (próxima viagem planejada)
│   ├── RelatorioController.php
│   ├── UnidadesController.php          # matriz/filial (CNPJ/IE/endereço próprios por unidade)
│   ├── UsersController.php
│   ├── VeiculosController.php
│   ├── ViagensController.php
│   ├── Auth/                           # login, 2FA, reset de senha (admin)
│   ├── Concerns/
│   │   └── GeraComprovanteAcerto.php   # PDF do comprovante, reaproveitado pelo admin e pelo portal
│   ├── Portal/                         # controllers exclusivos do Portal do Motorista
│   │   ├── PortalAuthController.php
│   │   ├── PortalLancamentosController.php
│   │   ├── PortalSenhaController.php
│   │   └── PortalViagensController.php
│   └── Webhooks/
│       ├── AsaasWebhookController.php       # status de cobrança recorrente
│       └── FocusNfeWebhookController.php    # status de emissão de CT-e/MDF-e
├── Http/Middleware/
│   ├── EnsureUserIsAdmin.php
│   ├── EnsureUserIsSuperAdmin.php      # restringe telas de gestão de empresas ao super admin
│   └── EnsureUserIsNotSuperAdmin.php   # super admin não acessa telas operacionais (são por empresa)
├── Support/
│   ├── TenantContext.php               # resolve a empresa do usuário/motorista autenticado no momento
│   └── CsvImporter.php                 # importação em massa (motoristas/veículos/clientes), transacional
├── Listeners/
│   └── LogAcessoListener.php          # grava IP/data-hora a cada login (Marco Civil, Art. 15)
├── Services/
│   ├── Asaas/
│   │   ├── AsaasClient.php             # cobrança recorrente (assinatura por empresa)
│   │   └── PlanoPricing.php            # tabela de planos/valores
│   └── FocusNfe/
│       └── FocusNfeClient.php          # emissão/consulta/encerramento de CT-e/MDF-e
├── Models/
│   ├── Concerns/
│   │   ├── BelongsToEmpresa.php       # escopo global + preenchimento automático de empresa_id
│   │   ├── TracksUser.php             # created_by / updated_by automáticos (guard "web")
│   │   ├── TracksDeletingUser.php     # deleted_by automático (guard "web")
│   │   └── HasUploadedFile.php        # URL do arquivo, assinada se o disco for a nuvem
│   ├── Carga.php                      # agrupamento de NF-e por cliente (unidade de emissão do CT-e)
│   ├── Cliente.php
│   ├── Desconto.php
│   ├── DespesaGeral.php
│   ├── Documento.php
│   ├── EmissaoFiscal.php              # emissão real de CT-e/MDF-e via Focus NFe
│   ├── Empresa.php                    # tenant — empresa cliente da plataforma
│   ├── Lancamento.php
│   ├── LogAcesso.php                  # registro de login (IP, data/hora) para conformidade LGPD
│   ├── Manutencao.php
│   ├── Motorista.php                  # Authenticatable — guard próprio do Portal
│   ├── ProgramacaoViagem.php          # próxima viagem planejada (Programação de Frota)
│   ├── Unidade.php                    # matriz/filial — CNPJ/IE/endereço fiscal próprios
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
├── auth/
├── clientes/
├── components/                          # inputs/botões reaproveitados nos formulários de auth/portal
├── despesas-gerais/
├── diagnostico/                          # tela de saúde do servidor/aplicação (super admin)
├── dre/
├── emissoes-fiscais/                    # telas /cte e /mdfe (mesma view, parametrizada por tipo)
├── empresas/                            # telas de gestão de empresas (super admin), inclui Unidades
├── layouts/                             # app.blade.php (admin), guest.blade.php (auth), portal.blade.php
├── legal/                               # termos de uso, política de privacidade
├── manutencoes/
├── motoristas/
├── portal/                              # auth/, viagens/, senha/ — telas do motorista
├── profile/
├── programacoes/                        # Programação de Frota
├── relatorios/
├── users/
├── veiculos/
├── viagens/                              # inclui os blocos de Carga na tela de detalhe
├── dashboard.blade.php
└── landing.blade.php
database/
└── migrations/
tests/
├── Unit/Models/
└── Feature/
    ├── Empresas/                        # testes do CRUD de empresas, Dados Fiscais, Unidades
    ├── EmissoesFiscais/                 # testes das telas /cte e /mdfe
    ├── Viagens/                         # inclui Cargas, Documentos, Emissões Fiscais
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