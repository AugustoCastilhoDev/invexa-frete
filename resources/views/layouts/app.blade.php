<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚛 {{ config('app.name') }} - @yield('title', 'Dashboard')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
            width: 250px;
            position: fixed;
            top: 0; left: 0;
            z-index: 100;
            padding-top: 20px;
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
        .main-content {
            margin-left: 250px;
            padding: 30px;
            min-height: 100vh;
        }
        .topbar {
            background: #fff;
            border-bottom: 1px solid #e9ecef;
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

        .pagination { margin-bottom: 0; }
        .page-link { color: #f97316; padding: .25rem .6rem; font-size: .8rem; }
        .page-item.active .page-link { background: #f97316; border-color: #f97316; }
        .page-link:hover { color: #ea6c0a; }
    </style>
</head>
<body>

{{-- Sidebar --}}
<div class="sidebar">
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
    </nav>

    <div class="nav-section">Relatórios</div>
    <nav class="nav flex-column">
        <a class="nav-link {{ request()->routeIs('relatorios.*') ? 'active' : '' }}"
        href="{{ route('relatorios.index') }}">
            <i class="bi bi-bar-chart-line"></i> Financeiro
        </a>
        <a class="nav-link {{ request()->routeIs('acertos.*') ? 'active' : '' }}"
        href="{{ route('acertos.index') }}">
            <i class="bi bi-person-check"></i> Acertos
        </a>
        <a class="nav-link {{ request()->routeIs('dre.*') ? 'active' : '' }}"
        href="{{ route('dre.index') }}">
            <i class="bi bi-clipboard-data"></i> DRE
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
        <a class="nav-link {{ request()->routeIs('clientes.*') ? 'active' : '' }}"
            href="{{ route('clientes.index') }}">
            <i class="bi bi-building"></i> Clientes
        </a>
        <a class="nav-link {{ request()->routeIs('despesas-gerais.*') ? 'active' : '' }}"
            href="{{ route('despesas-gerais.index') }}">
            <i class="bi bi-receipt"></i> Despesas Gerais
        </a>
    </nav>

    @if(auth()->user()?->isAdmin())
    <div class="nav-section">Administração</div>
    <nav class="nav flex-column">
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
</div>

{{-- Topbar --}}
<div class="topbar">
    <h6 class="mb-0 fw-semibold">
        <i class="bi bi-chevron-right text-muted me-1" style="font-size:.7rem"></i>
        @yield('title', 'Dashboard')
    </h6>
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

        <i class="bi bi-person-circle text-muted"></i>
        <span class="text-muted small">{{ Auth::user()?->name }}</span>
    </div>
</div>

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
</script>
@stack('scripts')

{{-- ── Footer ── --}}
<footer style="
    margin-left:250px;
    background:#fff;
    border-top:1px solid #e9ecef;
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