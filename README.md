<p align="center">
  <img src="https://raw.githubusercontent.com/AugustoCastilhoDev/invexa-frete/main/public/favicon.ico" width="80" alt="Invexa Frete">
</p>

<h1 align="center">Invexa Frete</h1>

<p align="center">
  Sistema de Gestão de Frotas da linha <strong>Invexa</strong> — controle de veículos, motoristas, clientes e viagens em uma plataforma moderna e intuitiva.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13.x-FF2D20?style=flat-square&logo=laravel" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.3-777BB4?style=flat-square&logo=php" alt="PHP">
  <img src="https://img.shields.io/badge/Tailwind_CSS-3.x-38BDF8?style=flat-square&logo=tailwindcss" alt="TailwindCSS">
  <img src="https://img.shields.io/badge/Vite-5.x-646CFF?style=flat-square&logo=vite" alt="Vite">
  <img src="https://img.shields.io/badge/licença-MIT-green?style=flat-square" alt="MIT">
</p>

---

## 📦 Sobre o Projeto

O **Invexa Frete** é o segundo produto da linha **Invexa**, voltado para empresas de transporte e logística. Ele oferece uma solução completa para gestão de frotas, incluindo cadastro de veículos, controle de motoristas, gerenciamento de clientes e acompanhamento de viagens — tudo com dashboard analítico em tempo real.

## ✨ Funcionalidades

- 🚛 **Veículos** — Cadastro e controle completo da frota
- 👤 **Motoristas** — Gestão de motoristas e suas habilitações
- 🧑‍💼 **Clientes** — Cadastro de clientes e vinculação às viagens
- 🗺️ **Viagens** — Registro e acompanhamento de viagens
- 📊 **Dashboard** — Gráficos e indicadores de desempenho
- 📄 **Exportação PDF** — Geração de relatórios via DomPDF
- 🔐 **Autenticação** — Login, registro e controle de acesso via Laravel Breeze
- 🌐 **Internacionalização** — Suporte ao idioma pt-BR via laravel-lang

## 🛠️ Tecnologias

| Camada | Tecnologia |
|---|---|
| Backend | Laravel 13 / PHP 8.3 |
| Frontend | Blade + Tailwind CSS + Vite |
| Autenticação | Laravel Breeze |
| PDF | barryvdh/laravel-dompdf |
| I18n | laravel-lang/common |
| Filas | Laravel Queue |
| Testes | PHPUnit 12 |

## ⚙️ Instalação

### Pré-requisitos

- PHP >= 8.3
- Composer
- Node.js >= 18
- MySQL ou SQLite

### Passo a passo

```bash
# 1. Clone o repositório
git clone https://github.com/AugustoCastilhoDev/invexa-frete.git
cd invexa-frete

# 2. Instale as dependências e configure o ambiente automaticamente
composer run setup
```

> O script `setup` instala dependências PHP e Node, copia o `.env.example`, gera a chave da aplicação, executa as migrations e compila os assets.

### Configuração manual (alternativa)

```bash
composer install
cp .env.example .env
php artisan key:generate
# Configure o banco de dados no .env, depois:
php artisan migrate
npm install
npm run build
```

## 🚀 Executando em Desenvolvimento

```bash
composer run dev
```

Este comando sobe em paralelo o servidor Laravel, worker de filas, log watcher (Pail) e o Vite HMR.

## 🧪 Testes

```bash
composer run test
```

## 📁 Estrutura de Pastas

```
app/
├── Http/Controllers/   # Controllers (Veiculos, Motoristas, Clientes, Viagens...)
├── Models/             # Models Eloquent
resources/
├── views/              # Templates Blade
database/
├── migrations/         # Migrations do banco
routes/
└── web.php             # Rotas da aplicação
```

## 🔧 Variáveis de Ambiente

Copie o `.env.example` e configure as principais variáveis:

```env
APP_NAME="Invexa Frete"
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=invexa_frete
DB_USERNAME=root
DB_PASSWORD=

MESSAGE_STACK=queue
```

## 🤝 Contribuição

Este é um projeto privado da linha de produtos **Invexa**. Para sugestões ou reporte de problemas, entre em contato com o time de desenvolvimento.

## 📄 Licença

Distribuído sob a licença MIT. Consulte o arquivo `LICENSE` para mais detalhes.

---

<p align="center">Desenvolvido por <strong>Augusto Castilho</strong> · <a href="https://github.com/AugustoCastilhoDev">@AugustoCastilhoDev</a></p>
