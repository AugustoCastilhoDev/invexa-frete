<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title') — Invexa Frete</title>
    <link rel="icon" href="/favicon.png" type="image/png">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Figtree', system-ui, sans-serif; background:#f8f9fa; }
        .invexa-hero-bg { background: radial-gradient(circle at 15% 10%, #24314f 0%, #16213e 45%, #1a1a2e 100%); }
        .invexa-logo-badge { background: linear-gradient(135deg, #f97316, #ea580c); box-shadow: 0 8px 20px rgba(249, 115, 22, .4); }
        .legal-content h2 { font-size: 1.15rem; font-weight: 700; margin-top: 2rem; margin-bottom: .75rem; color: #1a1a2e; }
        .legal-content h2:first-child { margin-top: 0; }
        .legal-content p, .legal-content li { color: #495057; line-height: 1.7; font-size: .95rem; }
        .legal-content ul { padding-left: 1.25rem; }
    </style>
</head>
<body>
    <header class="invexa-hero-bg" style="border-bottom:1px solid rgba(255,255,255,.08)">
        <div class="mx-auto flex items-center justify-between" style="max-width:1180px; padding:14px 24px; display:flex">
            <a href="{{ route('landing') }}" class="flex items-center gap-2 no-underline" style="display:flex; align-items:center; gap:8px">
                <div class="invexa-logo-badge rounded-2xl flex items-center justify-center" style="width:38px;height:38px;flex-shrink:0; display:flex; align-items:center; justify-content:center">
                    <i class="bi bi-truck-front-fill text-white" style="font-size:1.15rem"></i>
                </div>
                <div>
                    <div class="font-bold text-white" style="font-size:1.15rem; line-height:1">
                        Invexa <span class="text-orange-500">Frete</span>
                    </div>
                    <div class="text-white/40" style="font-size:.6rem; letter-spacing:.06em; text-transform:uppercase">Gestão de Viagens</div>
                </div>
            </a>
            <a href="{{ route('landing') }}" class="text-white/80 no-underline" style="font-size:.85rem">
                <i class="bi bi-arrow-left me-1"></i>Voltar ao site
            </a>
        </div>
    </header>

    <main class="mx-auto" style="max-width:820px; padding:48px 24px 64px">
        <div class="bg-white rounded-4 shadow-sm" style="padding:40px 44px">
            <h1 class="fw-bold" style="font-size:1.6rem; color:#1a1a2e">@yield('title')</h1>
            <p class="text-muted" style="font-size:.85rem">Última atualização: {{ $atualizadoEm ?? '08/07/2026' }}</p>
            <hr class="my-4">
            <div class="legal-content">
                @yield('content')
            </div>
        </div>
    </main>

    <footer style="background:#fff; border-top:1px solid #e9ecef; padding:24px">
        <div class="mx-auto d-flex justify-content-between align-items-center flex-wrap" style="max-width:1180px; gap:10px">
            <div class="d-flex align-items-center gap-2">
                <div class="invexa-logo-badge rounded-2 d-flex align-items-center justify-content-center" style="width:28px;height:28px;flex-shrink:0">
                    <i class="bi bi-truck-front-fill text-white" style="font-size:.75rem"></i>
                </div>
                <span style="font-size:.8rem; color:#6c757d">
                    Desenvolvido por
                    <a href="https://www.instagram.com/castilho_digital/" target="_blank" rel="noopener noreferrer"
                       style="color:#f97316; font-weight:600; text-decoration:none">
                        <i class="bi bi-instagram me-1"></i>Castilho Soluções Digitais
                    </a>
                </span>
            </div>
            <div style="font-size:.75rem; color:#adb5bd">
                <i class="bi bi-truck-front me-1"></i>Invexa Frete &copy; {{ date('Y') }}
            </div>
            <div style="font-size:.8rem; color:#6c757d">
                <i class="bi bi-envelope me-1 text-muted"></i>Suporte:
                <a href="mailto:contato@invexa-app.com.br" style="color:#f97316; font-weight:600; text-decoration:none">
                    contato@invexa-app.com.br
                </a>
            </div>
        </div>
    </footer>
</body>
</html>
