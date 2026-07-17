<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - @yield('title', 'Dashboard')</title>
    <link rel="icon" href="/favicon.png" type="image/png">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <script>
        // Aplica o tema salvo antes do CSS renderizar, evitando "flash" de tema errado
        document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('tema') || 'light');
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --if-body-bg: #f8f9fa;
            --if-topbar-bg: #fff;
            --if-topbar-border: #e9ecef;
            --if-footer-bg: #fff;
            --if-footer-border: #e9ecef;
        }
        html[data-bs-theme="dark"] {
            --if-body-bg: #10131a;
            --if-topbar-bg: #1a1d27;
            --if-topbar-border: #2a2e3a;
            --if-footer-bg: #1a1d27;
            --if-footer-border: #2a2e3a;
        }
        body { background-color: var(--if-body-bg); }
        .sidebar {
            height: 100vh;
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
            width: 250px;
            position: fixed;
            top: 0; left: 0;
            z-index: 100;
            padding-top: 20px;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }
        .sidebar .brand {
            color: #fff;
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: block;
            text-decoration: none;
            transition: opacity .2s;
        }
        .sidebar .brand:hover { opacity: .85; }
        .sidebar .brand span { color: #f97316; }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.7);
            padding: 10px 20px;
            border-radius: 8px;
            margin: 2px 10px;
            transition: all 0.2s;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(249,115,22,0.2);
            color: #f97316;
        }
        .sidebar .nav-link i { margin-right: 10px; font-size: 1.1rem; }
        .sidebar .nav-section {
            color: rgba(255,255,255,0.3);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 15px 20px 5px;
        }
        .sidebar-datetime {
            margin: 4px 20px 10px;
            padding: 10px 12px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 8px;
            color: rgba(255,255,255,0.65);
            font-size: .75rem;
            line-height: 1.5;
        }
        .sidebar-datetime .sidebar-time {
            display: block;
            font-size: 1rem;
            font-weight: 600;
            color: #fff;
        }
        .sidebar-user {
            margin-top: auto;
            border-top: 1px solid rgba(255,255,255,0.1);
            padding: 10px;
        }
        .sidebar-user-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            border: none;
            background: transparent;
            border-radius: 8px;
            padding: 8px 10px;
            text-align: left;
            transition: background .2s;
        }
        .sidebar-user-btn:hover { background: rgba(255,255,255,0.08); }
        .sidebar-user-avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: rgba(249,115,22,0.2);
            color: #f97316;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            font-size: 1.1rem;
        }
        .sidebar-user-info { min-width: 0; flex: 1; }
        .sidebar-user-name {
            display: block;
            color: #fff;
            font-size: .85rem;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sidebar-user-hint {
            display: block;
            color: rgba(255,255,255,0.5);
            font-size: .7rem;
        }
        .main-content {
            margin-left: 250px;
            padding: 30px;
            min-height: 100vh;
        }
        .topbar {
            background: var(--if-topbar-bg);
            border-bottom: 1px solid var(--if-topbar-border);
            padding: 12px 30px;
            margin-left: 250px;
            position: sticky;
            top: 0;
            z-index: 99;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .badge-status-aberta        { background:#3b82f6; }
        .badge-status-em_andamento  { background:#f59e0b; }
        .badge-status-aguardando_acerto { background:#8b5cf6; }
        .badge-status-encerrada     { background:#10b981; }
        .card { border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.08); border-radius: 12px; }
        .card-header { border-radius: 12px 12px 0 0 !important; }
        .btn-primary { background:#f97316; border-color:#f97316; }
        .btn-primary:hover { background:#ea6c0a; border-color:#ea6c0a; }
        .table th { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; }

        /* Modo escuro: componentes que fixam bg-white perdem contraste com o corpo do card,
           que o Bootstrap já escurece sozinho via data-bs-theme. */
        html[data-bs-theme="dark"] .card-header.bg-white,
        html[data-bs-theme="dark"] .card-footer.bg-white,
        html[data-bs-theme="dark"] .input-group-text.bg-white {
            background-color: var(--bs-tertiary-bg) !important;
            color: var(--bs-body-color) !important;
            border-color: var(--bs-border-color) !important;
        }
        /* Campos readonly com bg-light (ex: prévia de valor calculado) também fixam cor
           clara, mas herdam o texto claro do tema escuro — ficando ilegíveis */
        html[data-bs-theme="dark"] .form-control.bg-light {
            background-color: var(--bs-tertiary-bg) !important;
            color: var(--bs-body-color) !important;
            border-color: var(--bs-border-color) !important;
        }
        /* table-light (cabeçalho das listagens) também é uma cor fixa, não reage ao tema */
        html[data-bs-theme="dark"] .table-light {
            --bs-table-bg: var(--bs-tertiary-bg);
            --bs-table-color: var(--bs-body-color);
            --bs-table-border-color: var(--bs-border-color);
        }
        /* btn-outline-dark (usado nos botões de "Exportar PDF") fica ilegível no escuro */
        html[data-bs-theme="dark"] .btn-outline-dark {
            color: #e9ecef;
            border-color: #6c757d;
        }
        html[data-bs-theme="dark"] .btn-outline-dark:hover {
            background-color: #495057;
            border-color: #6c757d;
            color: #fff;
        }
        /* Cards de saldo (Acertos) com gradiente claro fixo — precisam de uma versão escura */
        .card-saldo-pendente { background: linear-gradient(135deg,#fffbeb,#fef3c7); }
        .card-saldo-pago { background: linear-gradient(135deg,#f0fdf4,#dcfce7); }
        html[data-bs-theme="dark"] .card-saldo-pendente { background: linear-gradient(135deg,#3a2e10,#4d3d10); }
        html[data-bs-theme="dark"] .card-saldo-pago { background: linear-gradient(135deg,#0f2e1c,#123c22); }

        .pagination { margin-bottom: 0; }
        .page-link { color: #f97316; padding: .25rem .6rem; font-size: .8rem; }
        .page-item.active .page-link { background: #f97316; border-color: #f97316; }
        .page-link:hover { color: #ea6c0a; }

        .suporte-banner {
            position: fixed; top: 0; left: 0; right: 0; z-index: 200;
            background: #b45309; color: #fff; text-align: center;
            padding: 8px 20px; font-size: .85rem;
        }
        .suporte-banner form { display: inline; }
        body.modo-suporte .sidebar { top: 38px; height: calc(100vh - 38px); }
        body.modo-suporte .topbar { top: 38px; }
        body.modo-suporte .main-content { padding-top: 68px; }

        .sidebar-toggle { display: none; }
        .sidebar-overlay { display: none; }
        .sidebar-close { display: none; }

        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform .25s ease;
            }
            .sidebar.show { transform: translateX(0); }
            .main-content, .topbar, footer { margin-left: 0 !important; }
            .topbar { padding: 12px 16px; }
            .main-content { padding: 20px 16px; }
            .sidebar-toggle {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 38px; height: 38px;
                border: none; background: transparent;
                color: #495057; font-size: 1.3rem;
            }
            .sidebar-overlay.show {
                display: block;
                position: fixed; inset: 0;
                background: rgba(0,0,0,.4);
                z-index: 99;
            }
            .sidebar-close {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                position: absolute;
                top: 16px; right: 16px;
                width: 32px; height: 32px;
                border: none; border-radius: 8px;
                background: rgba(255,255,255,.1);
                color: #fff; font-size: 1.1rem;
            }
        }
    </style>
</head>
<body class="{{ session('suporte_super_admin_id') ? 'modo-suporte' : '' }}">

@if(session('suporte_super_admin_id'))
<div class="suporte-banner">
    <i class="bi bi-shield-exclamation me-1"></i>
    Acesso de <strong>suporte</strong> — você está vendo o sistema como administrador de
    <strong>{{ session('suporte_empresa_nome') }}</strong>.
    <form action="{{ route('suporte.encerrar') }}" method="POST" class="ms-2">
        @csrf
        <button type="submit" class="btn btn-sm btn-dark">Encerrar suporte</button>
    </form>
</div>
@endif

{{-- Sidebar --}}
<div class="sidebar">
    <button type="button" class="sidebar-close" id="sidebarClose" aria-label="Fechar menu">
        <i class="bi bi-x-lg"></i>
    </button>
    <a class="brand" href="{{ route('dashboard') }}">
    <div class="d-flex align-items-center gap-2">
        <div style="
            width:36px;height:36px;
            background:linear-gradient(135deg,#f97316,#ea580c);
            border-radius:8px;
            display:flex;align-items:center;justify-content:center;
            flex-shrink:0;
            box-shadow:0 2px 8px rgba(249,115,22,.4)">
            <i class="bi bi-truck-front-fill text-white" style="font-size:1.1rem"></i>
        </div>
        <div>
            <div style="font-size:1.1rem;font-weight:700;line-height:1">
                Invexa <span>Frete</span>
            </div>
            <div style="font-size:.6rem;color:rgba(255,255,255,.4);font-weight:400;letter-spacing:.5px">
                GESTÃO DE VIAGENS
            </div>
        </div>
    </div>
</a>

    <div class="sidebar-datetime" id="sidebarDateTime">
        <span class="sidebar-time" id="sidebarClockTime">--:--</span>
        <span id="sidebarClockDate">-</span>
    </div>

    @if(auth()->user()?->isSuperAdmin())
    <div class="nav-section">Plataforma</div>
    <nav class="nav flex-column">
        <a class="nav-link {{ request()->routeIs('empresas.*') ? 'active' : '' }}"
        href="{{ route('empresas.index') }}">
            <i class="bi bi-buildings"></i> Empresas
        </a>
    </nav>
    @else
    <div class="nav-section">Principal</div>
    <nav class="nav flex-column">
        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
        href="{{ route('dashboard') }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a class="nav-link {{ request()->routeIs('viagens.*') ? 'active' : '' }}"
        href="{{ route('viagens.index') }}">
            <i class="bi bi-truck"></i> Viagens
        </a>
        <a class="nav-link {{ request()->routeIs('programacoes.*') ? 'active' : '' }}"
        href="{{ route('programacoes.index') }}">
            <i class="bi bi-signpost-2"></i> Programação de Frota
        </a>
        <a class="nav-link {{ request()->routeIs('acertos.*') ? 'active' : '' }}"
        href="{{ route('acertos.index') }}">
            <i class="bi bi-person-check"></i> Acertos
        </a>
    </nav>

    <div class="nav-section">Cadastros</div>
    <nav class="nav flex-column">
        <a class="nav-link {{ request()->routeIs('motoristas.*') ? 'active' : '' }}"
           href="{{ route('motoristas.index') }}">
            <i class="bi bi-person-badge"></i> Motoristas
        </a>
        <a class="nav-link {{ request()->routeIs('veiculos.*') ? 'active' : '' }}"
           href="{{ route('veiculos.index') }}">
            <i class="bi bi-car-front"></i> Veículos
        </a>
        <a class="nav-link {{ request()->routeIs('manutencoes.index') ? 'active' : '' }}"
           href="{{ route('manutencoes.index') }}">
            <i class="bi bi-tools"></i> Histórico de Manutenções
        </a>
        <a class="nav-link {{ request()->routeIs('clientes.*') ? 'active' : '' }}"
            href="{{ route('clientes.index') }}">
            <i class="bi bi-building"></i> Clientes
        </a>
    </nav>

    <div class="nav-section">Fiscal</div>
    <nav class="nav flex-column">
        <a class="nav-link {{ request()->routeIs('emissoes-fiscais.*') ? 'active' : '' }}"
           href="{{ route('emissoes-fiscais.index') }}">
            <i class="bi bi-file-earmark-check"></i> Emissões Fiscais
        </a>
    </nav>

    @if(auth()->user()?->isAdmin())
    <div class="nav-section">Administração</div>
    <nav class="nav flex-column">
        <a class="nav-link {{ request()->routeIs('relatorios.*') ? 'active' : '' }}"
        href="{{ route('relatorios.index') }}">
            <i class="bi bi-bar-chart-line"></i> Financeiro
        </a>
        <a class="nav-link {{ request()->routeIs('dre.*') ? 'active' : '' }}"
        href="{{ route('dre.index') }}">
            <i class="bi bi-clipboard-data"></i> DRE
        </a>
        <a class="nav-link {{ request()->routeIs('despesas-gerais.*') ? 'active' : '' }}"
            href="{{ route('despesas-gerais.index') }}">
            <i class="bi bi-receipt"></i> Despesas Gerais
        </a>
        <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
           href="{{ route('users.index') }}">
            <i class="bi bi-people"></i> Usuários
        </a>
    </nav>
    @endif
    @endif

    <div class="nav-section">Conta</div>
    <nav class="nav flex-column">
        <a class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}"
           href="{{ route('profile.edit') }}">
            <i class="bi bi-person-circle"></i> Meu Perfil
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="nav-link border-0 bg-transparent w-100 text-start">
                <i class="bi bi-box-arrow-left"></i> Sair
            </button>
        </form>
    </nav>

    <div class="sidebar-user">
        <button type="button" class="sidebar-user-btn" id="themeToggle" title="Alternar tema claro/escuro">
            <span class="sidebar-user-avatar"><i class="bi bi-person-fill"></i></span>
            <span class="sidebar-user-info">
                <span class="sidebar-user-name">{{ Auth::user()?->name }}</span>
                <span class="sidebar-user-hint">
                    <i class="bi bi-moon-stars" id="themeToggleIcon"></i>
                    <span id="themeToggleLabel">Modo escuro</span>
                </span>
            </span>
        </button>
    </div>
</div>

{{-- Topbar --}}
<div class="topbar">
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Abrir menu">
            <i class="bi bi-list"></i>
        </button>
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-chevron-right text-muted me-1" style="font-size:.7rem"></i>
            @yield('title', 'Dashboard')
        </h6>
    </div>
    <div class="d-flex align-items-center gap-3">
        <div class="dropdown">
            <button class="btn btn-link text-muted position-relative p-0" type="button"
                    data-bs-toggle="dropdown" aria-expanded="false" title="Notificações">
                <i class="bi bi-bell" style="font-size:1.2rem"></i>
                @if($notificacoesNaoLidas->count() > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                      style="font-size:.6rem">
                    {{ $notificacoesNaoLidas->count() > 9 ? '9+' : $notificacoesNaoLidas->count() }}
                </span>
                @endif
            </button>
            <div class="dropdown-menu dropdown-menu-end p-0" style="width:340px;max-height:420px;overflow-y:auto">
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                    <span class="fw-semibold small">Notificações</span>
                    @if($notificacoesNaoLidas->count() > 0)
                    <form method="POST" action="{{ route('notificacoes.ler-todas') }}">
                        @csrf
                        <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none" style="font-size:.75rem">
                            Marcar todas como lidas
                        </button>
                    </form>
                    @endif
                </div>
                @forelse($notificacoesNaoLidas as $notificacao)
                <form method="POST" action="{{ route('notificacoes.ler', $notificacao->id) }}">
                    @csrf
                    <button type="submit" class="dropdown-item py-2 border-bottom" style="white-space:normal">
                        <div class="fw-semibold small">{{ $notificacao->data['titulo'] ?? 'Notificação' }}</div>
                        <div class="text-muted" style="font-size:.75rem">{{ $notificacao->data['mensagem'] ?? '' }}</div>
                        <div class="text-muted" style="font-size:.65rem">{{ $notificacao->created_at->diffForHumans() }}</div>
                    </button>
                </form>
                @empty
                <div class="text-center text-muted small py-4">Nenhuma notificação pendente</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

{{-- Conteúdo --}}
<div class="main-content">

    {{-- Alertas --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>
            @foreach($errors->all() as $erro)
                {{ $erro }}@if(! $loop->last)<br>@endif
            @endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Alterna entre valor mascarado e completo em dados sensíveis (CPF/CNH)
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.toggle-dado-sensivel');
        if (!btn) return;

        const span = btn.previousElementSibling;
        const mostrando = span.textContent === span.dataset.completo;

        span.textContent = mostrando ? span.dataset.mascarado : span.dataset.completo;
        btn.querySelector('i').className = mostrando ? 'bi bi-eye' : 'bi bi-eye-slash';
    });

    // Sidebar off-canvas no mobile
    (function () {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const toggle = document.getElementById('sidebarToggle');
        const close = document.getElementById('sidebarClose');

        function closeSidebar() {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        }

        toggle?.addEventListener('click', function () {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        });

        overlay?.addEventListener('click', closeSidebar);
        close?.addEventListener('click', closeSidebar);
    })();

    // Relógio/data ao vivo no sidebar
    (function () {
        const timeEl = document.getElementById('sidebarClockTime');
        const dateEl = document.getElementById('sidebarClockDate');
        if (!timeEl || !dateEl) return;

        function atualizar() {
            const agora = new Date();
            timeEl.textContent = agora.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
            dateEl.textContent = agora.toLocaleDateString('pt-BR', { weekday: 'long', day: '2-digit', month: 'long' });
        }

        atualizar();
        setInterval(atualizar, 1000);
    })();

    // Alternância de tema claro/escuro (Bootstrap color modes)
    (function () {
        const btn = document.getElementById('themeToggle');
        const icon = document.getElementById('themeToggleIcon');
        const label = document.getElementById('themeToggleLabel');
        if (!btn) return;

        function aplicar(tema) {
            document.documentElement.setAttribute('data-bs-theme', tema);
            icon.className = tema === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';
            label.textContent = tema === 'dark' ? 'Modo claro' : 'Modo escuro';
        }

        aplicar(localStorage.getItem('tema') || 'light');

        btn.addEventListener('click', function () {
            const novoTema = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            localStorage.setItem('tema', novoTema);
            aplicar(novoTema);
        });
    })();
</script>
@stack('scripts')

{{-- ── Footer ── --}}
<footer style="
    margin-left:250px;
    background:var(--if-footer-bg);
    border-top:1px solid var(--if-footer-border);
    padding:16px 30px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;
    gap:8px;">

    {{-- Lado esquerdo: créditos --}}
    <div class="d-flex align-items-center gap-2">
        <div style="
            width:28px;height:28px;
            background:linear-gradient(135deg,#f97316,#ea580c);
            border-radius:6px;
            display:flex;align-items:center;justify-content:center;
            flex-shrink:0">
            <i class="bi bi-truck-front-fill text-white" style="font-size:.75rem"></i>
        </div>
        <span style="font-size:.8rem;color:#6c757d">
            Desenvolvido por
            <a href="https://www.instagram.com/castilho_digital/"
               target="_blank"
               rel="noopener noreferrer"
               style="color:#f97316;font-weight:600;text-decoration:none">
                <i class="bi bi-instagram me-1"></i>Castilho Soluções Digitais
            </a>
        </span>
    </div>

    {{-- Centro: versão --}}
    <div style="font-size:.75rem;color:#adb5bd">
        <i class="bi bi-truck-front me-1"></i>
        Invexa Frete &copy; {{ date('Y') }}
        ·
        <a href="{{ route('legal.termos') }}" style="color:#adb5bd;text-decoration:none">Termos de Uso</a>
        ·
        <a href="{{ route('legal.privacidade') }}" style="color:#adb5bd;text-decoration:none">Política de Privacidade</a>
    </div>

    {{-- Lado direito: suporte --}}
    <div style="font-size:.8rem;color:#6c757d">
        <i class="bi bi-envelope me-1 text-muted"></i>
        Suporte:
        <a href="mailto:contato@invexa-app.com.br"
           style="color:#f97316;font-weight:600;text-decoration:none">
            contato@invexa-app.com.br
        </a>
    </div>

</footer>

</body>
</html>