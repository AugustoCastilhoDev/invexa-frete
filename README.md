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
- Lançamento de combustível, manutenção e outros gastos
- Controle de KM inicial e final
- Adiantamento ao motorista (com opção de desconto ou não)
- Descontos (vale, multa, outros)
- Documentos fiscais (CT-e, MDF-e, NF-e)
- Impressão de comprovante de acerto em PDF

### 👤 Motoristas
- Cadastro completo (CPF, CNH, categoria, validade)
- Percentual de comissão padrão por motorista
- Histórico de viagens por motorista
- Busca por nome, CPF ou telefone

### 🚛 Veículos
- Cadastro completo da frota (placa, modelo, marca, RENAVAM)
- Controle de status (ativo, inativo, em manutenção)
- Histórico de viagens por veículo
- Busca por placa, modelo ou marca

### 🧑‍💼 Clientes
- Cadastro de Pessoa Física e Jurídica
- Busca automática de endereço por CEP (ViaCEP)
- Tabela de frete padrão por cliente
- Vinculação direta às viagens
- Busca por nome, CNPJ/CPF, cidade ou telefone

### 📊 Dashboard
- Cards de resumo: viagens abertas, faturamento, lucro, frota
- Gráfico de Faturamento vs Lucro com filtros de 30, 60, 90 dias e período personalizado
- Gráfico de Viagens por Status
- Viagens em aberto
- Top 5 motoristas do mês

### 📈 Relatórios
- Relatório Financeiro por período com filtros avançados
- Resumo por motorista com paginação
- Composição de despesas em gráfico
- Exportação em PDF (landscape)
- Acertos por Motorista com histórico individual
- Separação de saldo a pagar vs total já pago

---

## 🛠️ Tecnologias

| Camada | Tecnologia |
|--------|------------|
| Backend | Laravel 13 / PHP 8.3 |
| Frontend | Blade + Bootstrap 5.3 + Bootstrap Icons |
| Build | Vite 5.x |
| Banco de dados | MySQL 8.0 |
| Autenticação | Laravel Breeze |
| PDF | barryvdh/laravel-dompdf |
| Internacionalização | laravel-lang/common (pt_BR) |
| Gráficos | Chart.js |
| CEP | ViaCEP API |

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

## 👤 Criando o primeiro usuário

```bash
php artisan tinker
```

```php
App\Models\User::create([
    'name'     => 'Administrador',
    'email'    => 'admin@invexafrete.com',
    'password' => bcrypt('sua_senha'),
]);
```

---

## 🚀 Deploy em Produção (VPS)

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
```

---

## 📁 Estrutura de Pastas
app/
├── Http/Controllers/
│   ├── AcertosController.php
│   ├── ClientesController.php
│   ├── DashboardController.php
│   ├── DescontosController.php
│   ├── DocumentosController.php
│   ├── LancamentosController.php
│   ├── MotoristasController.php
│   ├── RelatorioController.php
│   ├── VeiculosController.php
│   └── ViagensController.php
├── Models/
│   ├── Cliente.php
│   ├── Desconto.php
│   ├── Documento.php
│   ├── Lancamento.php
│   ├── Motorista.php
│   ├── Veiculo.php
│   └── Viagem.php
resources/
└── views/
├── acertos/
├── clientes/
├── layouts/
├── motoristas/
├── relatorios/
├── veiculos/
└── viagens/
database/
└── migrations/
routes/
└── web.php

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