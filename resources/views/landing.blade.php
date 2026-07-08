<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistema de gestão de viagens para transportadoras: motoristas, veículos, financeiro, DRE e portal do motorista em uma única plataforma.">

    <title>Invexa Frete — Gestão completa para transportadoras</title>
    <link rel="icon" href="/favicon.png" type="image/png">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Figtree', system-ui, sans-serif; }

        .invexa-hero-bg {
            background: radial-gradient(circle at 15% 10%, #24314f 0%, #16213e 45%, #1a1a2e 100%);
        }
        .invexa-logo-badge {
            background: linear-gradient(135deg, #f97316, #ea580c);
            box-shadow: 0 8px 20px rgba(249, 115, 22, .4);
        }
        .invexa-btn-primary {
            background: linear-gradient(135deg, #f97316, #ea580c);
            box-shadow: 0 8px 20px rgba(249, 115, 22, .35);
            transition: transform .15s ease, box-shadow .15s ease;
        }
        .invexa-btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 25px rgba(249, 115, 22, .45);
        }
        .invexa-feature-card {
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
            border: 1px solid #eef0f3;
        }
        .invexa-feature-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 30px -12px rgba(22, 33, 62, .18);
            border-color: rgba(249, 115, 22, .25);
        }
        .invexa-plan-card {
            border: 1px solid #e9ecef;
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }
        .invexa-plan-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -14px rgba(22, 33, 62, .2);
        }
        .invexa-plan-card.is-featured {
            border: 2px solid #f97316;
            box-shadow: 0 20px 40px -14px rgba(249, 115, 22, .25);
        }
        .invexa-icon-chip {
            width: 3rem; height: 3rem;
            background: linear-gradient(135deg, rgba(249,115,22,.12), rgba(234,88,12,.12));
            color: #ea580c;
        }

        .invexa-mockup-frame {
            border: 1px solid #e9ecef;
            box-shadow: 0 30px 60px -20px rgba(22, 33, 62, .3);
        }
        .invexa-mockup-navitem {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 14px; border-radius: 8px;
            color: rgba(255,255,255,.55); font-size: .82rem;
        }
        .invexa-mockup-navitem.is-active {
            background: rgba(249,115,22,.15); color: #fff; font-weight: 600;
        }
        .invexa-mockup-kpi {
            border: 1px solid #eef0f3; border-radius: 12px; padding: 14px 16px;
        }
        .invexa-mockup-donut {
            width: 108px; height: 108px; border-radius: 50%;
            background: conic-gradient(#3b82f6 0 18%, #f59e0b 18% 44%, #8b5cf6 44% 58%, #10b981 58% 100%);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .invexa-mockup-donut span {
            width: 62px; height: 62px; border-radius: 50%; background: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: .68rem; font-weight: 700; color: #16213e; text-align: center;
        }
        .invexa-mockup-tabs {
            display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; margin-bottom: 24px;
        }
        .invexa-mockup-tab {
            border: 1px solid #e2e5e9; background: #fff; color: #495057; border-radius: 999px;
            padding: 9px 18px; font-size: .83rem; font-weight: 600; cursor: pointer;
            transition: all .15s ease;
        }
        .invexa-mockup-tab:hover { border-color: #f97316; color: #ea580c; }
        .invexa-mockup-tab.is-active {
            background: linear-gradient(135deg, #f97316, #ea580c); border-color: transparent; color: #fff;
            box-shadow: 0 8px 20px rgba(249, 115, 22, .3);
        }
        .invexa-mockup-panel { display: none; }
        .invexa-mockup-panel.is-active { display: block; }
        .invexa-phone-frame {
            width: 280px; margin: 0 auto; border: 10px solid #1a1a2e; border-radius: 34px;
            overflow: hidden; box-shadow: 0 30px 60px -20px rgba(22, 33, 62, .35); background: #1a1a2e;
        }
        .invexa-mockup-chip {
            background: #fff; border: 1px solid #e2e5e9; border-radius: 8px;
            padding: 6px 12px; font-size: .72rem; color: #495057;
        }
        .invexa-mockup-table th {
            font-size: .68rem; text-transform: uppercase; letter-spacing: .03em;
            color: #868e96; font-weight: 600; background: #f8f9fa; white-space: nowrap;
        }
        .invexa-mockup-table td { font-size: .78rem; color: #495057; white-space: nowrap; vertical-align: middle; }
    </style>
</head>
<body class="text-gray-900 antialiased" style="background:#f8f9fa">

    {{-- ── Header ── --}}
    <header class="invexa-hero-bg" style="position:sticky; top:0; z-index:50; border-bottom:1px solid rgba(255,255,255,.08)">
        <div class="mx-auto flex items-center justify-between" style="max-width:1180px; padding:14px 24px">
            <a href="/" class="flex items-center gap-2 no-underline">
                <div class="invexa-logo-badge rounded-2xl flex items-center justify-center" style="width:38px;height:38px;flex-shrink:0">
                    <i class="bi bi-truck-front-fill text-white" style="font-size:1.15rem"></i>
                </div>
                <div>
                    <div class="font-bold text-white" style="font-size:1.15rem; line-height:1">
                        Invexa <span class="text-orange-500">Frete</span>
                    </div>
                    <div class="text-white/40" style="font-size:.6rem; letter-spacing:.06em; text-transform:uppercase">Gestão de Viagens</div>
                </div>
            </a>

            <nav class="hidden md:flex items-center gap-6" style="font-size:.9rem">
                <a href="#recursos" class="text-white/70 no-underline" style="transition:color .15s">Recursos</a>
                <a href="#planos" class="text-white/70 no-underline">Planos</a>
                <a href="#contato" class="text-white/70 no-underline">Contato</a>
            </nav>

            <div class="flex items-center" style="gap:10px">
                <a href="{{ route('portal.login') }}" class="d-flex align-items-center text-white/80 no-underline" style="gap:4px; font-size:.8rem">
                    <i class="bi bi-phone"></i>
                    <span class="d-none d-sm-inline">Portal do Motorista</span>
                    <span class="d-sm-none">Portal</span>
                </a>
                <a href="{{ route('login') }}" class="text-white/80 no-underline" style="font-size:.85rem">Entrar</a>
                <a href="https://wa.me/5532999669302?text=Ol%C3%A1%2C%20quero%20conhecer%20o%20Invexa%20Frete"
                   target="_blank" rel="noopener noreferrer"
                   class="invexa-btn-primary text-white no-underline rounded-full font-semibold text-nowrap"
                   style="padding:9px 16px; font-size:.8rem">
                    Falar com Vendas
                </a>
            </div>
        </div>
    </header>

    {{-- ── Hero ── --}}
    <section class="invexa-hero-bg" style="padding:76px 24px 96px">
        <div class="mx-auto text-center" style="max-width:760px">
            <span class="inline-block rounded-full text-orange-400" style="background:rgba(249,115,22,.12); border:1px solid rgba(249,115,22,.3); padding:6px 16px; font-size:.78rem; letter-spacing:.03em">
                <i class="bi bi-stars me-1"></i> Feito para transportadoras de todos os portes
            </span>

            <h1 class="font-extrabold text-white" style="font-size:2.6rem; line-height:1.15; margin-top:22px">
                A gestão da sua frota, das viagens e do financeiro num só lugar
            </h1>

            <p class="text-white/60" style="font-size:1.1rem; margin-top:18px; line-height:1.6">
                Do cadastro do motorista ao acerto financeiro: controle viagens, aprove lançamentos, acompanhe o DRE
                e dê acesso direto ao motorista pelo celular — tudo isolado com segurança por empresa.
            </p>

            <div class="flex items-center justify-center flex-wrap" style="gap:14px; margin-top:32px">
                <a href="https://wa.me/5532999669302?text=Ol%C3%A1%2C%20quero%20conhecer%20o%20Invexa%20Frete"
                   target="_blank" rel="noopener noreferrer"
                   class="invexa-btn-primary text-white no-underline rounded-full font-semibold"
                   style="padding:14px 30px; font-size:.95rem">
                    <i class="bi bi-whatsapp me-2"></i>Falar com Vendas
                </a>
                <a href="#planos" class="no-underline rounded-full font-semibold text-white"
                   style="padding:14px 30px; font-size:.95rem; border:1px solid rgba(255,255,255,.25)">
                    Ver planos e preços
                </a>
            </div>

            <p class="text-white/35" style="font-size:.8rem; margin-top:20px">
                <i class="bi bi-check-circle me-1"></i>Teste grátis por 14 dias
                <span class="mx-2">·</span>
                <i class="bi bi-shield-lock me-1"></i>Dados isolados por empresa
                <span class="mx-2">·</span>
                <i class="bi bi-file-earmark-lock me-1"></i>Conformidade com a LGPD
                <span class="mx-2">·</span>
                <i class="bi bi-cloud-check me-1"></i>Backup diário automático
            </p>
        </div>
    </section>

    {{-- ── Demonstração (mockups ilustrativos do painel) ── --}}
    <section style="padding:0 24px 80px">
        <div class="mx-auto" style="max-width:980px">
            <div class="text-center" style="max-width:620px; margin:0 auto 28px">
                <h2 class="font-extrabold" style="font-size:1.6rem; color:#16213e">Veja como é por dentro</h2>
                <p class="text-muted" style="margin-top:8px">
                    Do painel administrativo ao celular do motorista — clique para navegar pelas telas.
                </p>
            </div>

            <div class="invexa-mockup-tabs">
                <button type="button" class="invexa-mockup-tab is-active" data-target="mockup-dashboard">
                    <i class="bi bi-speedometer2 me-1"></i>Dashboard
                </button>
                <button type="button" class="invexa-mockup-tab" data-target="mockup-viagens">
                    <i class="bi bi-truck me-1"></i>Viagens
                </button>
                <button type="button" class="invexa-mockup-tab" data-target="mockup-acerto">
                    <i class="bi bi-cash-coin me-1"></i>Acerto
                </button>
                <button type="button" class="invexa-mockup-tab" data-target="mockup-portal">
                    <i class="bi bi-phone me-1"></i>Portal do Motorista
                </button>
            </div>

            {{-- Painel: Dashboard --}}
            <div id="mockup-dashboard" class="invexa-mockup-panel is-active">
                <div class="invexa-mockup-frame rounded-4 bg-white overflow-hidden">
                    <div class="d-flex align-items-center" style="gap:8px; padding:10px 16px; background:#eef0f3; border-bottom:1px solid #e2e5e9">
                        <span style="width:10px;height:10px;border-radius:50%;background:#ff5f57;display:inline-block"></span>
                        <span style="width:10px;height:10px;border-radius:50%;background:#febc2e;display:inline-block"></span>
                        <span style="width:10px;height:10px;border-radius:50%;background:#28c840;display:inline-block"></span>
                        <span class="mx-auto d-none d-sm-inline-block text-muted" style="font-size:.72rem; background:#fff; border:1px solid #e2e5e9; border-radius:6px; padding:3px 14px">
                            <i class="bi bi-lock-fill me-1" style="font-size:.65rem"></i>app.invexafrete.com.br/dashboard
                        </span>
                    </div>

                    <div class="d-flex" style="min-height:420px">
                        <div class="d-none d-md-flex flex-column" style="width:190px; flex-shrink:0; background:linear-gradient(180deg,#1a1a2e 0%,#16213e 100%); padding:18px 12px">
                            <div class="d-flex align-items-center gap-2" style="padding:0 6px 18px">
                                <div class="invexa-logo-badge rounded-2 d-flex align-items-center justify-content-center" style="width:26px;height:26px;flex-shrink:0">
                                    <i class="bi bi-truck-front-fill text-white" style="font-size:.7rem"></i>
                                </div>
                                <span class="text-white fw-bold" style="font-size:.85rem">Invexa Frete</span>
                            </div>
                            <div class="invexa-mockup-navitem is-active"><i class="bi bi-speedometer2"></i>Dashboard</div>
                            <div class="invexa-mockup-navitem"><i class="bi bi-truck"></i>Viagens</div>
                            <div class="invexa-mockup-navitem"><i class="bi bi-person-badge"></i>Motoristas</div>
                            <div class="invexa-mockup-navitem"><i class="bi bi-car-front"></i>Veículos</div>
                            <div class="invexa-mockup-navitem"><i class="bi bi-cash-coin"></i>Financeiro</div>
                            <div class="invexa-mockup-navitem"><i class="bi bi-phone"></i>Portal do Motorista</div>
                        </div>

                        <div style="flex:1; padding:22px 24px; background:#fbfbfc; min-width:0">
                            <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:8px; margin-bottom:16px">
                                <span class="fw-bold" style="font-size:1.05rem; color:#16213e">Dashboard</span>
                                <span class="text-muted" style="font-size:.75rem">Julho de 2026</span>
                            </div>

                            <div class="grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(120px, 1fr)); gap:10px; margin-bottom:20px">
                                <div class="invexa-mockup-kpi">
                                    <div class="text-muted" style="font-size:.7rem">Abertas</div>
                                    <div class="fw-bold" style="font-size:1.3rem; color:#3b82f6">8</div>
                                </div>
                                <div class="invexa-mockup-kpi">
                                    <div class="text-muted" style="font-size:.7rem">Em andamento</div>
                                    <div class="fw-bold" style="font-size:1.3rem; color:#f59e0b">14</div>
                                </div>
                                <div class="invexa-mockup-kpi">
                                    <div class="text-muted" style="font-size:.7rem">Aguard. acerto</div>
                                    <div class="fw-bold" style="font-size:1.3rem; color:#8b5cf6">5</div>
                                </div>
                                <div class="invexa-mockup-kpi">
                                    <div class="text-muted" style="font-size:.7rem">Encerradas no mês</div>
                                    <div class="fw-bold" style="font-size:1.3rem; color:#10b981">42</div>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap" style="gap:18px">
                                <div style="flex:1 1 320px; min-width:0">
                                    <div class="text-muted fw-semibold" style="font-size:.72rem; text-transform:uppercase; letter-spacing:.03em; margin-bottom:8px">Últimas viagens</div>
                                    <div class="rounded-3" style="border:1px solid #eef0f3; overflow:hidden">
                                        @php
                                            $viagensDemo = [
                                                ['motorista' => 'Carlos Silva', 'rota' => 'BH → SP', 'status' => 'Em andamento', 'cor' => '#f59e0b'],
                                                ['motorista' => 'João Pereira', 'rota' => 'RJ → SP', 'status' => 'Aguard. acerto', 'cor' => '#8b5cf6'],
                                                ['motorista' => 'Marcos Souza', 'rota' => 'Curitiba → Joinville', 'status' => 'Encerrada', 'cor' => '#10b981'],
                                                ['motorista' => 'Roberto Lima', 'rota' => 'Uberlândia → Goiânia', 'status' => 'Aberta', 'cor' => '#3b82f6'],
                                            ];
                                        @endphp
                                        @foreach($viagensDemo as $i => $v)
                                        <div class="d-flex align-items-center justify-content-between" style="padding:10px 12px; {{ $i > 0 ? 'border-top:1px solid #f1f3f5' : '' }}">
                                            <div style="min-width:0">
                                                <div class="fw-semibold text-truncate" style="font-size:.8rem; color:#16213e">{{ $v['motorista'] }}</div>
                                                <div class="text-muted text-truncate" style="font-size:.72rem">{{ $v['rota'] }}</div>
                                            </div>
                                            <span class="badge rounded-pill text-white flex-shrink-0" style="background:{{ $v['cor'] }}; font-size:.65rem; margin-left:8px">{{ $v['status'] }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="d-flex align-items-center" style="gap:16px; flex:0 1 auto">
                                    <div class="invexa-mockup-donut"><span>69<br>viagens</span></div>
                                    <div style="font-size:.72rem; color:#495057">
                                        <div class="d-flex align-items-center" style="gap:6px; padding:2px 0"><span style="width:8px;height:8px;border-radius:50%;background:#3b82f6;display:inline-block"></span>Abertas</div>
                                        <div class="d-flex align-items-center" style="gap:6px; padding:2px 0"><span style="width:8px;height:8px;border-radius:50%;background:#f59e0b;display:inline-block"></span>Em andamento</div>
                                        <div class="d-flex align-items-center" style="gap:6px; padding:2px 0"><span style="width:8px;height:8px;border-radius:50%;background:#8b5cf6;display:inline-block"></span>Aguard. acerto</div>
                                        <div class="d-flex align-items-center" style="gap:6px; padding:2px 0"><span style="width:8px;height:8px;border-radius:50%;background:#10b981;display:inline-block"></span>Encerradas</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Painel: Viagens --}}
            <div id="mockup-viagens" class="invexa-mockup-panel">
                <div class="invexa-mockup-frame rounded-4 bg-white overflow-hidden">
                    <div class="d-flex align-items-center" style="gap:8px; padding:10px 16px; background:#eef0f3; border-bottom:1px solid #e2e5e9">
                        <span style="width:10px;height:10px;border-radius:50%;background:#ff5f57;display:inline-block"></span>
                        <span style="width:10px;height:10px;border-radius:50%;background:#febc2e;display:inline-block"></span>
                        <span style="width:10px;height:10px;border-radius:50%;background:#28c840;display:inline-block"></span>
                        <span class="mx-auto d-none d-sm-inline-block text-muted" style="font-size:.72rem; background:#fff; border:1px solid #e2e5e9; border-radius:6px; padding:3px 14px">
                            <i class="bi bi-lock-fill me-1" style="font-size:.65rem"></i>app.invexafrete.com.br/viagens
                        </span>
                    </div>

                    <div class="d-flex" style="min-height:420px">
                        <div class="d-none d-md-flex flex-column" style="width:190px; flex-shrink:0; background:linear-gradient(180deg,#1a1a2e 0%,#16213e 100%); padding:18px 12px">
                            <div class="d-flex align-items-center gap-2" style="padding:0 6px 18px">
                                <div class="invexa-logo-badge rounded-2 d-flex align-items-center justify-content-center" style="width:26px;height:26px;flex-shrink:0">
                                    <i class="bi bi-truck-front-fill text-white" style="font-size:.7rem"></i>
                                </div>
                                <span class="text-white fw-bold" style="font-size:.85rem">Invexa Frete</span>
                            </div>
                            <div class="invexa-mockup-navitem"><i class="bi bi-speedometer2"></i>Dashboard</div>
                            <div class="invexa-mockup-navitem is-active"><i class="bi bi-truck"></i>Viagens</div>
                            <div class="invexa-mockup-navitem"><i class="bi bi-person-badge"></i>Motoristas</div>
                            <div class="invexa-mockup-navitem"><i class="bi bi-car-front"></i>Veículos</div>
                            <div class="invexa-mockup-navitem"><i class="bi bi-cash-coin"></i>Financeiro</div>
                            <div class="invexa-mockup-navitem"><i class="bi bi-phone"></i>Portal do Motorista</div>
                        </div>

                        <div style="flex:1; padding:22px 24px; background:#fbfbfc; min-width:0">
                            <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:8px; margin-bottom:14px">
                                <span class="fw-bold" style="font-size:1.05rem; color:#16213e">Viagens</span>
                                <div class="d-flex flex-wrap" style="gap:8px">
                                    <span class="invexa-mockup-chip">Status: Todos</span>
                                    <span class="invexa-mockup-chip">Motorista: Todos</span>
                                    <span class="invexa-mockup-chip"><i class="bi bi-calendar3 me-1"></i>01/07 – 07/07</span>
                                </div>
                            </div>

                            <div class="rounded-3" style="border:1px solid #eef0f3; overflow-x:auto">
                                <table class="invexa-mockup-table" style="width:100%; border-collapse:collapse">
                                    <thead>
                                        <tr>
                                            <th style="padding:8px 12px; text-align:left">Motorista</th>
                                            <th style="padding:8px 12px; text-align:left">Veículo</th>
                                            <th style="padding:8px 12px; text-align:left">Rota</th>
                                            <th style="padding:8px 12px; text-align:left">Saída</th>
                                            <th style="padding:8px 12px; text-align:left">Frete</th>
                                            <th style="padding:8px 12px; text-align:left">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $viagensLista = [
                                                ['motorista' => 'Carlos Silva', 'veiculo' => 'ABC-1234', 'rota' => 'BH → SP', 'saida' => '02/07', 'frete' => '3.200,00', 'status' => 'Em andamento', 'cor' => '#f59e0b'],
                                                ['motorista' => 'João Pereira', 'veiculo' => 'XYZ-9988', 'rota' => 'RJ → SP', 'saida' => '03/07', 'frete' => '1.850,00', 'status' => 'Aguard. acerto', 'cor' => '#8b5cf6'],
                                                ['motorista' => 'Marcos Souza', 'veiculo' => 'JKL-4521', 'rota' => 'Curitiba → Joinville', 'saida' => '30/06', 'frete' => '980,00', 'status' => 'Encerrada', 'cor' => '#10b981'],
                                                ['motorista' => 'Roberto Lima', 'veiculo' => 'QWE-7788', 'rota' => 'Uberlândia → Goiânia', 'saida' => '01/07', 'frete' => '2.400,00', 'status' => 'Aberta', 'cor' => '#3b82f6'],
                                                ['motorista' => 'Ana Costa', 'veiculo' => 'RTY-3345', 'rota' => 'BH → Vitória', 'saida' => '28/06', 'frete' => '1.560,00', 'status' => 'Encerrada', 'cor' => '#10b981'],
                                            ];
                                        @endphp
                                        @foreach($viagensLista as $i => $v)
                                        <tr style="{{ $i > 0 ? 'border-top:1px solid #f1f3f5' : '' }}">
                                            <td style="padding:9px 12px; font-weight:600; color:#16213e">{{ $v['motorista'] }}</td>
                                            <td style="padding:9px 12px">{{ $v['veiculo'] }}</td>
                                            <td style="padding:9px 12px">{{ $v['rota'] }}</td>
                                            <td style="padding:9px 12px">{{ $v['saida'] }}</td>
                                            <td style="padding:9px 12px">R$ {{ $v['frete'] }}</td>
                                            <td style="padding:9px 12px">
                                                <span class="badge rounded-pill text-white" style="background:{{ $v['cor'] }}; font-size:.65rem">{{ $v['status'] }}</span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Painel: Acerto --}}
            <div id="mockup-acerto" class="invexa-mockup-panel">
                <div class="invexa-mockup-frame rounded-4 bg-white overflow-hidden">
                    <div class="d-flex align-items-center" style="gap:8px; padding:10px 16px; background:#eef0f3; border-bottom:1px solid #e2e5e9">
                        <span style="width:10px;height:10px;border-radius:50%;background:#ff5f57;display:inline-block"></span>
                        <span style="width:10px;height:10px;border-radius:50%;background:#febc2e;display:inline-block"></span>
                        <span style="width:10px;height:10px;border-radius:50%;background:#28c840;display:inline-block"></span>
                        <span class="mx-auto d-none d-sm-inline-block text-muted" style="font-size:.72rem; background:#fff; border:1px solid #e2e5e9; border-radius:6px; padding:3px 14px">
                            <i class="bi bi-lock-fill me-1" style="font-size:.65rem"></i>app.invexafrete.com.br/acertos
                        </span>
                    </div>

                    <div class="d-flex" style="min-height:420px">
                        <div class="d-none d-md-flex flex-column" style="width:190px; flex-shrink:0; background:linear-gradient(180deg,#1a1a2e 0%,#16213e 100%); padding:18px 12px">
                            <div class="d-flex align-items-center gap-2" style="padding:0 6px 18px">
                                <div class="invexa-logo-badge rounded-2 d-flex align-items-center justify-content-center" style="width:26px;height:26px;flex-shrink:0">
                                    <i class="bi bi-truck-front-fill text-white" style="font-size:.7rem"></i>
                                </div>
                                <span class="text-white fw-bold" style="font-size:.85rem">Invexa Frete</span>
                            </div>
                            <div class="invexa-mockup-navitem"><i class="bi bi-speedometer2"></i>Dashboard</div>
                            <div class="invexa-mockup-navitem"><i class="bi bi-truck"></i>Viagens</div>
                            <div class="invexa-mockup-navitem"><i class="bi bi-person-badge"></i>Motoristas</div>
                            <div class="invexa-mockup-navitem"><i class="bi bi-car-front"></i>Veículos</div>
                            <div class="invexa-mockup-navitem is-active"><i class="bi bi-cash-coin"></i>Financeiro</div>
                            <div class="invexa-mockup-navitem"><i class="bi bi-phone"></i>Portal do Motorista</div>
                        </div>

                        <div style="flex:1; padding:22px 24px; background:#fbfbfc; min-width:0">
                            <div class="fw-bold" style="font-size:1.05rem; color:#16213e; margin-bottom:14px">Acerto do Motorista</div>

                            <div class="d-flex align-items-center" style="gap:12px; padding:12px 14px; border:1px solid #eef0f3; border-radius:12px; margin-bottom:16px; background:#fff">
                                <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#1a1a2e,#f97316);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                    <i class="bi bi-person-fill text-white" style="font-size:1rem"></i>
                                </div>
                                <div style="min-width:0">
                                    <div class="fw-semibold text-truncate" style="font-size:.85rem; color:#16213e">Carlos Silva</div>
                                    <div class="text-muted text-truncate" style="font-size:.7rem">CPF ***.456.789-** · CNH válida até 03/2027</div>
                                </div>
                                <span class="badge rounded-pill text-white ms-auto flex-shrink-0" style="background:#10b981; font-size:.62rem">Ativo</span>
                            </div>

                            <div class="grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(120px, 1fr)); gap:10px; margin-bottom:18px">
                                <div class="invexa-mockup-kpi">
                                    <div class="text-muted" style="font-size:.7rem">Viagens</div>
                                    <div class="fw-bold" style="font-size:1.2rem; color:#3b82f6">12</div>
                                </div>
                                <div class="invexa-mockup-kpi">
                                    <div class="text-muted" style="font-size:.7rem">Total Frete</div>
                                    <div class="fw-bold" style="font-size:1.2rem; color:#f97316">R$ 28.400</div>
                                </div>
                                <div class="invexa-mockup-kpi" style="background:linear-gradient(135deg,#fffbeb,#fef3c7); border-color:transparent">
                                    <div style="font-size:.7rem; color:#92400e">Saldo a Pagar</div>
                                    <div class="fw-bold" style="font-size:1.2rem; color:#b45309">R$ 2.520</div>
                                </div>
                                <div class="invexa-mockup-kpi" style="background:linear-gradient(135deg,#f0fdf4,#dcfce7); border-color:transparent">
                                    <div style="font-size:.7rem; color:#166534">Total Pago</div>
                                    <div class="fw-bold" style="font-size:1.2rem; color:#15803d">R$ 18.900</div>
                                </div>
                            </div>

                            <div class="text-muted fw-semibold" style="font-size:.72rem; text-transform:uppercase; letter-spacing:.03em; margin-bottom:8px">Viagens no período</div>
                            <div class="rounded-3" style="border:1px solid #eef0f3; overflow-x:auto">
                                <table class="invexa-mockup-table" style="width:100%; border-collapse:collapse">
                                    <thead>
                                        <tr>
                                            <th style="padding:8px 12px; text-align:left">Rota</th>
                                            <th style="padding:8px 12px; text-align:left">Frete</th>
                                            <th style="padding:8px 12px; text-align:left">Comissão</th>
                                            <th style="padding:8px 12px; text-align:left">Desconto</th>
                                            <th style="padding:8px 12px; text-align:left">Saldo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $acertoLinhas = [
                                                ['rota' => 'BH → SP', 'frete' => '3.200,00', 'comissao' => '320,00', 'desconto' => '0,00', 'saldo' => '2.880,00'],
                                                ['rota' => 'RJ → SP', 'frete' => '1.850,00', 'comissao' => '185,00', 'desconto' => '50,00', 'saldo' => '1.615,00'],
                                                ['rota' => 'Curitiba → Joinville', 'frete' => '980,00', 'comissao' => '98,00', 'desconto' => '0,00', 'saldo' => '882,00'],
                                            ];
                                        @endphp
                                        @foreach($acertoLinhas as $i => $l)
                                        <tr style="{{ $i > 0 ? 'border-top:1px solid #f1f3f5' : '' }}">
                                            <td style="padding:9px 12px">{{ $l['rota'] }}</td>
                                            <td style="padding:9px 12px">R$ {{ $l['frete'] }}</td>
                                            <td style="padding:9px 12px; color:#d97706">R$ {{ $l['comissao'] }}</td>
                                            <td style="padding:9px 12px; color:#dc2626">R$ {{ $l['desconto'] }}</td>
                                            <td style="padding:9px 12px; font-weight:600; color:#16213e">R$ {{ $l['saldo'] }}</td>
                                        </tr>
                                        @endforeach
                                        <tr style="border-top:2px solid #eef0f3">
                                            <td style="padding:9px 12px; font-weight:700; color:#16213e">Total</td>
                                            <td style="padding:9px 12px; font-weight:700; color:#16213e">R$ 6.030,00</td>
                                            <td style="padding:9px 12px; font-weight:700; color:#d97706">R$ 603,00</td>
                                            <td style="padding:9px 12px; font-weight:700; color:#dc2626">R$ 50,00</td>
                                            <td style="padding:9px 12px; font-weight:700; color:#16213e">R$ 5.377,00</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Painel: Portal do Motorista --}}
            <div id="mockup-portal" class="invexa-mockup-panel">
                <div class="invexa-phone-frame">
                    <div style="height:22px; display:flex; align-items:center; justify-content:center">
                        <div style="width:56px; height:5px; border-radius:3px; background:#333"></div>
                    </div>
                    <div style="background:#f8f9fa">
                        <div style="background:linear-gradient(180deg,#1a1a2e 0%,#16213e 100%); padding:14px 16px; display:flex; align-items:center; justify-content:space-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="invexa-logo-badge rounded-2 d-flex align-items-center justify-content-center" style="width:24px;height:24px;flex-shrink:0">
                                    <i class="bi bi-truck-front-fill text-white" style="font-size:.62rem"></i>
                                </div>
                                <span class="text-white fw-bold" style="font-size:.75rem">Invexa Frete</span>
                            </div>
                            <span class="text-white-50" style="font-size:.68rem">Roberto Lima</span>
                        </div>

                        <div style="padding:16px; min-height:400px">
                            <div class="fw-bold" style="font-size:.92rem; color:#16213e">Olá, Roberto 👋</div>
                            <div class="text-muted" style="font-size:.7rem; margin-bottom:14px">Suas viagens</div>

                            @php
                                $portalViagens = [
                                    ['rota' => 'BH → SP', 'saida' => '02/07', 'frete' => '3.200,00', 'saldo' => '2.850,00', 'status' => 'Encerrada', 'cor' => '#10b981'],
                                    ['rota' => 'Uberlândia → Goiânia', 'saida' => '05/07', 'frete' => '2.400,00', 'saldo' => null, 'status' => 'Em andamento', 'cor' => '#f59e0b'],
                                    ['rota' => 'Curitiba → Joinville', 'saida' => '07/07', 'frete' => '980,00', 'saldo' => null, 'status' => 'Aguard. acerto', 'cor' => '#8b5cf6'],
                                ];
                            @endphp
                            @foreach($portalViagens as $v)
                            <div class="bg-white rounded-3" style="padding:12px 14px; margin-bottom:10px; box-shadow:0 1px 3px rgba(0,0,0,.06)">
                                <div class="d-flex align-items-center justify-content-between" style="margin-bottom:4px">
                                    <span class="fw-semibold" style="font-size:.8rem; color:#16213e">{{ $v['rota'] }}</span>
                                    <span class="badge rounded-pill text-white flex-shrink-0" style="background:{{ $v['cor'] }}; font-size:.6rem; margin-left:8px">{{ $v['status'] }}</span>
                                </div>
                                <div class="text-muted" style="font-size:.7rem">
                                    Saída {{ $v['saida'] }} · R$ {{ $v['frete'] }}{{ $v['saldo'] ? ' · Saldo R$ '.$v['saldo'] : '' }}
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-around" style="padding:10px 0; border-top:1px solid #eef0f3; background:#fff">
                            <div class="text-center" style="color:#f97316">
                                <i class="bi bi-truck" style="font-size:1rem"></i>
                                <div style="font-size:.6rem">Viagens</div>
                            </div>
                            <div class="text-center text-muted">
                                <i class="bi bi-key" style="font-size:1rem"></i>
                                <div style="font-size:.6rem">Senha</div>
                            </div>
                            <div class="text-center text-muted">
                                <i class="bi bi-box-arrow-left" style="font-size:1rem"></i>
                                <div style="font-size:.6rem">Sair</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <p class="text-center text-muted" style="font-size:.72rem; margin-top:18px">
                * Imagens meramente ilustrativas — dados fictícios de demonstração, não representam clientes reais.
            </p>
        </div>
    </section>

    {{-- ── Recursos ── --}}
    <section id="recursos" style="padding:80px 24px">
        <div class="mx-auto" style="max-width:1100px">
            <div class="text-center" style="max-width:620px; margin:0 auto 48px">
                <h2 class="font-extrabold" style="font-size:1.9rem; color:#16213e">Tudo que a operação precisa, sem planilha</h2>
                <p class="text-muted" style="margin-top:10px">Da abertura da viagem ao acerto com o motorista, com aprovação, financeiro e documentos fiscais integrados.</p>
            </div>

            <div class="grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:22px">
                @php
                    $recursos = [
                        ['icon' => 'bi-truck', 'titulo' => 'Gestão de Viagens', 'texto' => 'Do status Aberta até Encerrada, com lançamentos, descontos, adiantamento e assinatura digital do motorista no comprovante.'],
                        ['icon' => 'bi-bar-chart-line', 'titulo' => 'Financeiro & DRE', 'texto' => 'Relatórios por período, acertos por motorista, despesas gerais e DRE simplificado — receita, custos e resultado líquido.'],
                        ['icon' => 'bi-building-lock', 'titulo' => 'Multiempresa & Segurança', 'texto' => 'Cada transportadora com os dados totalmente isolados, autenticação em dois fatores e mascaramento de CPF/CNH.'],
                        ['icon' => 'bi-phone', 'titulo' => 'Portal do Motorista', 'texto' => 'O motorista acompanha as próprias viagens e lança combustível/manutenção com foto, direto do celular, sem instalar app.'],
                        ['icon' => 'bi-file-earmark-check', 'titulo' => 'Documentos Fiscais', 'texto' => 'CT-e, MDF-e e NF-e com verificação de autenticidade direto no portal oficial da SEFAZ pela chave de acesso.'],
                        ['icon' => 'bi-car-front', 'titulo' => 'Frota Completa', 'texto' => 'Veículos, manutenção preventiva/corretiva e conjunto cavalo + carreta contando como uma única unidade no plano.'],
                        ['icon' => 'bi-signpost-2', 'titulo' => 'Programação de Frota', 'texto' => 'Planeje o motorista, veículo e cliente da próxima viagem antes de encerrar a atual — sem deixar veículo parado entre um frete e outro.'],
                        ['icon' => 'bi-cash-coin', 'titulo' => 'Controle de Recebimento', 'texto' => 'Confirme o recebimento do frete do cliente com um clique, acompanhe pendências e exporte o relatório de contas a receber.'],
                    ];
                @endphp

                @foreach($recursos as $r)
                <div class="invexa-feature-card rounded-3 bg-white" style="padding:28px">
                    <div class="invexa-icon-chip rounded-3 flex items-center justify-center" style="display:flex">
                        <i class="bi {{ $r['icon'] }}" style="font-size:1.3rem"></i>
                    </div>
                    <h3 class="font-semibold" style="font-size:1.05rem; margin-top:16px; color:#16213e">{{ $r['titulo'] }}</h3>
                    <p class="text-muted" style="font-size:.9rem; margin-top:8px; line-height:1.55">{{ $r['texto'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── Planos ── --}}
    <section id="planos" style="padding:80px 24px; background:#f1f3f6">
        <div class="mx-auto" style="max-width:1180px">
            <div class="text-center" style="max-width:620px; margin:0 auto 12px">
                <h2 class="font-extrabold" style="font-size:1.9rem; color:#16213e">Planos por tamanho de frota</h2>
                <p class="text-muted" style="margin-top:10px">
                    Cavalo mecânico + carreta vinculada contam como um único veículo no limite do plano.
                    Preço fixo por faixa — sem cobrança por veículo cadastrado.
                </p>
            </div>

            <p class="text-center" style="font-size:.85rem; margin-bottom:40px">
                <span class="badge rounded-pill text-white" style="background:#16a34a; padding:7px 16px">
                    <i class="bi bi-gift me-1"></i>14 dias grátis para testar, sem cartão
                </span>
            </p>

            <div class="grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(255px, 1fr)); gap:22px">
                @php
                    $planos = [
                        [
                            'nome' => 'Starter', 'limite' => 'até 5 veículos',
                            'mensal' => '590', 'anual' => '492',
                            'destaque' => false,
                        ],
                        [
                            'nome' => 'Pro', 'limite' => 'até 15 veículos',
                            'mensal' => '1.290', 'anual' => '1.075',
                            'destaque' => true,
                        ],
                        [
                            'nome' => 'Business', 'limite' => 'até 30 veículos',
                            'mensal' => '2.190', 'anual' => '1.825',
                            'destaque' => false,
                        ],
                    ];
                @endphp

                @foreach($planos as $p)
                <div class="invexa-plan-card rounded-4 bg-white {{ $p['destaque'] ? 'is-featured' : '' }}" style="padding:32px 26px; position:relative">
                    @if($p['destaque'])
                    <span class="badge rounded-pill text-white" style="background:#f97316; position:absolute; top:-12px; left:50%; transform:translateX(-50%); padding:5px 16px; font-size:.72rem">
                        MAIS ESCOLHIDO
                    </span>
                    @endif

                    <div class="text-center">
                        <div class="fw-bold" style="font-size:1.2rem; color:#16213e">Plano {{ $p['nome'] }}</div>
                        <div class="text-muted" style="font-size:.85rem; margin-top:2px">{{ $p['limite'] }}</div>

                        <div style="margin-top:20px">
                            <span class="fw-bold" style="font-size:2.1rem; color:#16213e">R$ {{ $p['mensal'] }}</span>
                            <span class="text-muted" style="font-size:.85rem">/mês</span>
                        </div>
                        <div class="text-muted" style="font-size:.78rem; margin-top:4px">
                            ou R$ {{ $p['anual'] }}/mês no plano anual
                        </div>
                    </div>

                    <ul class="list-unstyled" style="margin-top:24px; font-size:.87rem; color:#495057">
                        <li style="padding:6px 0"><i class="bi bi-check-lg text-success me-2"></i>Viagens, financeiro e DRE completos</li>
                        <li style="padding:6px 0"><i class="bi bi-check-lg text-success me-2"></i>Portal do Motorista incluído</li>
                        <li style="padding:6px 0"><i class="bi bi-check-lg text-success me-2"></i>Usuários ilimitados da equipe</li>
                        <li style="padding:6px 0"><i class="bi bi-check-lg text-success me-2"></i>Suporte via WhatsApp</li>
                    </ul>

                    <a href="https://wa.me/5532999669302?text=Ol%C3%A1%2C%20tenho%20interesse%20no%20Plano%20{{ $p['nome'] }}%20da%20Invexa%20Frete"
                       target="_blank" rel="noopener noreferrer"
                       class="{{ $p['destaque'] ? 'invexa-btn-primary text-white' : 'text-orange-600' }} d-block text-center no-underline fw-semibold rounded-3"
                       style="margin-top:24px; padding:12px; {{ $p['destaque'] ? '' : 'border:1px solid #f97316' }}">
                        Falar com Vendas
                    </a>
                </div>
                @endforeach

                {{-- Enterprise --}}
                <div class="invexa-plan-card rounded-4 bg-white" style="padding:32px 26px">
                    <div class="text-center">
                        <div class="fw-bold" style="font-size:1.2rem; color:#16213e">Plano Enterprise</div>
                        <div class="text-muted" style="font-size:.85rem; margin-top:2px">acima de 30 veículos</div>
                        <div style="margin-top:20px">
                            <span class="fw-bold" style="font-size:1.5rem; color:#16213e">Sob consulta</span>
                        </div>
                        <div class="text-muted" style="font-size:.78rem; margin-top:4px">condições negociadas</div>
                    </div>

                    <ul class="list-unstyled" style="margin-top:24px; font-size:.87rem; color:#495057">
                        <li style="padding:6px 0"><i class="bi bi-check-lg text-success me-2"></i>Tudo do plano Business</li>
                        <li style="padding:6px 0"><i class="bi bi-check-lg text-success me-2"></i>Frota sem limite fixo</li>
                        <li style="padding:6px 0"><i class="bi bi-check-lg text-success me-2"></i>Atendimento dedicado</li>
                    </ul>

                    <a href="https://wa.me/5532999669302?text=Ol%C3%A1%2C%20tenho%20interesse%20no%20Plano%20Enterprise%20da%20Invexa%20Frete"
                       target="_blank" rel="noopener noreferrer"
                       class="text-orange-600 d-block text-center no-underline fw-semibold rounded-3"
                       style="margin-top:24px; padding:12px; border:1px solid #f97316">
                        Falar com Vendas
                    </a>
                </div>
            </div>

            <p class="text-center text-muted" style="font-size:.8rem; margin-top:32px">
                Taxa de implantação única de R$ 490 (grátis para quem assina o plano anual).
            </p>
        </div>
    </section>

    {{-- ── CTA final ── --}}
    <section id="contato" class="invexa-hero-bg" style="padding:70px 24px">
        <div class="mx-auto text-center" style="max-width:600px">
            <h2 class="font-extrabold text-white" style="font-size:1.7rem">
                Pronto para profissionalizar a gestão da sua transportadora?
            </h2>
            <p class="text-white/60" style="margin-top:12px">
                Fale com a gente e comece o teste grátis de 14 dias hoje mesmo.
            </p>

            <div class="flex items-center justify-center flex-wrap" style="gap:14px; margin-top:26px">
                <a href="https://wa.me/5532999669302?text=Ol%C3%A1%2C%20quero%20conhecer%20o%20Invexa%20Frete"
                   target="_blank" rel="noopener noreferrer"
                   class="invexa-btn-primary text-white no-underline rounded-full fw-semibold"
                   style="padding:14px 30px; font-size:.95rem">
                    <i class="bi bi-whatsapp me-2"></i>(32) 99966-9302
                </a>
                <a href="mailto:contato@invexa-app.com.br"
                   class="no-underline rounded-full fw-semibold text-white"
                   style="padding:14px 30px; font-size:.95rem; border:1px solid rgba(255,255,255,.25)">
                    <i class="bi bi-envelope me-2"></i>contato@invexa-app.com.br
                </a>
            </div>
        </div>
    </section>

    {{-- ── Footer ── --}}
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
                ·
                <a href="{{ route('legal.termos') }}" style="color:#adb5bd; text-decoration:none">Termos de Uso</a>
                ·
                <a href="{{ route('legal.privacidade') }}" style="color:#adb5bd; text-decoration:none">Política de Privacidade</a>
            </div>

            <div style="font-size:.8rem; color:#6c757d">
                <i class="bi bi-envelope me-1 text-muted"></i>Suporte:
                <a href="mailto:contato@invexa-app.com.br" style="color:#f97316; font-weight:600; text-decoration:none">
                    contato@invexa-app.com.br
                </a>
            </div>
        </div>
    </footer>

    <script>
        document.querySelectorAll('.invexa-mockup-tab').forEach(function (tab) {
            tab.addEventListener('click', function () {
                document.querySelectorAll('.invexa-mockup-tab').forEach(function (t) { t.classList.remove('is-active'); });
                document.querySelectorAll('.invexa-mockup-panel').forEach(function (p) { p.classList.remove('is-active'); });
                tab.classList.add('is-active');
                document.getElementById(tab.dataset.target).classList.add('is-active');
            });
        });
    </script>
</body>
</html>
