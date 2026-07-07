<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚛 {{ config('app.name') }} - @yield('title', 'Portal do Motorista')</title>
    <link rel="icon" href="/favicon.png" type="image/png">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .topbar {
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
            padding: 14px 0;
        }
        .topbar .brand { color: #fff; text-decoration: none; display: flex; align-items: center; gap: 10px; }
        .topbar .brand span { color: #f97316; }
        .topbar .nav-link { color: rgba(255,255,255,.7); }
        .topbar .nav-link:hover, .topbar .nav-link.active { color: #f97316; }
        .badge-status-aberta        { background:#3b82f6; }
        .badge-status-em_andamento  { background:#f59e0b; }
        .badge-status-aguardando_acerto { background:#8b5cf6; }
        .badge-status-encerrada     { background:#10b981; }
        .card { border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.08); border-radius: 12px; }
    </style>
</head>
<body>

<div class="topbar">
    <div class="container d-flex justify-content-between align-items-center">
        <a class="brand" href="{{ route('portal.viagens.index') }}">
            <div style="width:34px;height:34px;background:linear-gradient(135deg,#f97316,#ea580c);border-radius:8px;display:flex;align-items:center;justify-content:center">
                <i class="bi bi-truck-front-fill text-white"></i>
            </div>
            <span class="fw-bold">Invexa <span>Frete</span></span>
        </a>
        <nav class="d-flex align-items-center gap-3">
            <a class="nav-link {{ request()->routeIs('portal.viagens.*') ? 'active' : '' }}" href="{{ route('portal.viagens.index') }}">
                <i class="bi bi-truck me-1"></i> Minhas Viagens
            </a>
            <a class="nav-link {{ request()->routeIs('portal.senha.*') ? 'active' : '' }}" href="{{ route('portal.senha.edit') }}">
                <i class="bi bi-key me-1"></i> Trocar Senha
            </a>
            <span class="text-white-50 small">{{ Auth::guard('motorista')->user()->nome }}</span>
            <form method="POST" action="{{ route('portal.logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-light">
                    <i class="bi bi-box-arrow-left me-1"></i> Sair
                </button>
            </form>
        </nav>
    </div>
</div>

<div class="container py-4">
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

<footer class="text-center text-muted small py-3" style="border-top:1px solid #e9ecef">
    Invexa Frete &copy; {{ date('Y') }} — Portal do Motorista
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
