<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistema de gestão de viagens para transportadoras: motoristas, veículos, financeiro, DRE e portal do motorista em uma única plataforma.">

    <title>Invexa Frete — Gestão completa para transportadoras</title>

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

            <div class="flex items-center gap-3">
                <a href="{{ route('login') }}" class="text-white/80 no-underline" style="font-size:.9rem">Entrar</a>
                <a href="https://wa.me/5532999669302?text=Ol%C3%A1%2C%20quero%20conhecer%20o%20Invexa%20Frete"
                   target="_blank" rel="noopener noreferrer"
                   class="invexa-btn-primary text-white no-underline rounded-full font-semibold"
                   style="padding:9px 20px; font-size:.85rem">
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
