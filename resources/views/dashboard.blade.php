@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

{{-- ── Cards de Resumo ── --}}
<div class="row g-3 mb-4">

    <div class="col-md-4 col-lg-2">
        <div class="card h-100 border-start border-primary border-3">
            <div class="card-body">
                <div class="text-muted small mb-1">Viagens Abertas</div>
                <div class="fs-3 fw-bold text-primary">{{ $totalViagensAbertas }}</div>
                <div class="text-muted" style="font-size:.75rem">em andamento</div>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-lg-2">
        <div class="card h-100 border-start border-3" style="border-color:#8b5cf6!important">
            <div class="card-body">
                <div class="text-muted small mb-1">Aguard. Acerto</div>
                <div class="fs-3 fw-bold" style="color:#8b5cf6">{{ $totalAguardandoAcerto }}</div>
                <div class="text-muted" style="font-size:.75rem">a finalizar</div>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-lg-2">
        <div class="card h-100 border-start border-success border-3">
            <div class="card-body">
                <div class="text-muted small mb-1">Encerradas no Mês</div>
                <div class="fs-3 fw-bold text-success">{{ $totalViagensEncerradasMes }}</div>
                <div class="text-muted" style="font-size:.75rem">{{ now()->translatedFormat('F') }}</div>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-lg-2">
        <div class="card h-100 border-start border-3" style="border-color:#f97316!important">
            <div class="card-body">
                <div class="text-muted small mb-1">Faturamento do Mês</div>
                <div class="fs-5 fw-bold" style="color:#f97316">
                    R$ {{ number_format($faturamentoMes, 2, ',', '.') }}
                </div>
                <div class="text-muted" style="font-size:.75rem">fretes encerrados</div>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-lg-2">
        <div class="card h-100 border-start border-success border-3">
            <div class="card-body">
                <div class="text-muted small mb-1">Lucro do Mês</div>
                <div class="fs-5 fw-bold text-success">
                    R$ {{ number_format($lucroMes, 2, ',', '.') }}
                </div>
                <div class="text-muted" style="font-size:.75rem">líquido transportadora</div>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-lg-2">
        <div class="card h-100 border-start border-secondary border-3">
            <div class="card-body">
                <div class="text-muted small mb-1">Frota / Motoristas</div>
                <div class="fs-3 fw-bold text-secondary">
                    {{ $totalVeiculosAtivos }}<span class="fs-6 text-muted">/{{ $totalMotoristasAtivos }}</span>
                </div>
                <div class="text-muted" style="font-size:.75rem">veículos / motoristas</div>
            </div>
        </div>
    </div>

</div>

@php
    $totalPendencias = $cnhVencendo->count() + $veiculosEmManutencao->count() + $documentosPendentes->count() + $manutencoesVencendo->count();
@endphp
@if($totalPendencias > 0)
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border-start border-danger border-3">
            <div class="card-header bg-white fw-semibold d-flex align-items-center justify-content-between">
                <span><i class="bi bi-exclamation-triangle me-2 text-danger"></i>Pendências</span>
                <span class="badge bg-danger">{{ $totalPendencias }}</span>
            </div>
            <div class="card-body">
                <div class="row g-4">

                    {{-- CNH vencendo/vencida --}}
                    <div class="col-md-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-semibold small text-uppercase text-muted">
                                <i class="bi bi-person-badge me-1"></i>CNH a vencer
                            </span>
                            <span class="badge bg-secondary">{{ $cnhVencendo->count() }}</span>
                        </div>
                        @forelse($cnhVencendo as $motorista)
                            @php $vencida = $motorista->validade_cnh->isPast(); @endphp
                            <a href="{{ route('motoristas.show', $motorista) }}"
                               class="d-flex justify-content-between text-decoration-none py-1 small"
                               style="border-bottom:1px solid #f0f0f0">
                                <span class="text-dark">{{ $motorista->nome }}</span>
                                <span class="{{ $vencida ? 'text-danger fw-semibold' : 'text-warning' }}">
                                    {{ $vencida
                                        ? 'vencida há '.$motorista->validade_cnh->diffInDays(now()).'d'
                                        : 'vence em '.now()->diffInDays($motorista->validade_cnh).'d' }}
                                </span>
                            </a>
                        @empty
                            <p class="text-muted small mb-0">Nenhuma CNH vencendo nos próximos 30 dias.</p>
                        @endforelse
                    </div>

                    {{-- Veículos em manutenção --}}
                    <div class="col-md-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-semibold small text-uppercase text-muted">
                                <i class="bi bi-car-front me-1"></i>Veículos em manutenção
                            </span>
                            <span class="badge bg-secondary">{{ $veiculosEmManutencao->count() }}</span>
                        </div>
                        @forelse($veiculosEmManutencao as $veiculo)
                            <a href="{{ route('veiculos.show', $veiculo) }}"
                               class="d-flex justify-content-between text-decoration-none py-1 small"
                               style="border-bottom:1px solid #f0f0f0">
                                <span class="text-dark">{{ $veiculo->placa }}</span>
                                <span class="text-muted">{{ $veiculo->modelo }}</span>
                            </a>
                        @empty
                            <p class="text-muted small mb-0">Nenhum veículo em manutenção.</p>
                        @endforelse
                    </div>

                    {{-- Documentos fiscais pendentes --}}
                    <div class="col-md-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-semibold small text-uppercase text-muted">
                                <i class="bi bi-file-earmark-text me-1"></i>Documentos pendentes
                            </span>
                            <span class="badge bg-secondary">{{ $documentosPendentes->count() }}</span>
                        </div>
                        @forelse($documentosPendentes as $documento)
                            <a href="{{ route('viagens.show', $documento->viagem) }}"
                               class="d-flex justify-content-between text-decoration-none py-1 small"
                               style="border-bottom:1px solid #f0f0f0">
                                <span class="text-dark">{{ $documento->tipo_formatado }} {{ $documento->numero }}</span>
                                <span class="text-muted">{{ $documento->data_emissao->format('d/m/Y') }}</span>
                            </a>
                        @empty
                            <p class="text-muted small mb-0">Nenhum documento pendente.</p>
                        @endforelse
                    </div>

                    {{-- Manutenção preventiva vencendo --}}
                    <div class="col-md-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-semibold small text-uppercase text-muted">
                                <i class="bi bi-tools me-1"></i>Manutenção preventiva
                            </span>
                            <span class="badge bg-secondary">{{ $manutencoesVencendo->count() }}</span>
                        </div>
                        @forelse($manutencoesVencendo as $manutencao)
                            <a href="{{ route('veiculos.show', $manutencao->veiculo) }}"
                               class="d-flex justify-content-between text-decoration-none py-1 small"
                               style="border-bottom:1px solid #f0f0f0">
                                <span class="text-dark">{{ $manutencao->veiculo->placa }}</span>
                                <span class="text-muted">
                                    {{ $manutencao->proxima_manutencao_data->format('d/m/Y') }}
                                </span>
                            </a>
                        @empty
                            <p class="text-muted small mb-0">Nenhuma manutenção preventiva vencendo.</p>
                        @endforelse
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row g-4 mb-4">

    {{-- ── Gráfico de Faturamento ── --}}
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="fw-semibold">
                        <i class="bi bi-graph-up me-2 text-primary"></i>
                        Faturamento vs Lucro
                    </span>
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <div class="btn-group btn-group-sm" id="btnsPeriodo">
                            <button class="btn btn-primary active" data-periodo="30">30 dias</button>
                            <button class="btn btn-outline-secondary" data-periodo="60">60 dias</button>
                            <button class="btn btn-outline-secondary" data-periodo="90">90 dias</button>
                            <button class="btn btn-outline-secondary" data-periodo="personalizado">
                                <i class="bi bi-calendar3 me-1"></i>Personalizado
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Campos de data personalizado --}}
                <div id="periodoPersonalizado" class="d-none mt-2">
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <input type="date" id="dataInicio" class="form-control form-control-sm"
                               style="width:150px" value="{{ now()->subMonth()->format('Y-m-d') }}">
                        <span class="text-muted small">até</span>
                        <input type="date" id="dataFim" class="form-control form-control-sm"
                               style="width:150px" value="{{ now()->format('Y-m-d') }}">
                        <button class="btn btn-primary btn-sm" id="btnAplicar">
                            <i class="bi bi-search me-1"></i>Aplicar
                        </button>
                    </div>
                </div>

                {{-- Totalizadores do período --}}
                <div class="d-flex gap-4 mt-2">
                    <span class="small text-muted">
                        Faturamento:
                        <span id="totalFrete" class="fw-bold" style="color:#f97316">R$ 0,00</span>
                    </span>
                    <span class="small text-muted">
                        Lucro:
                        <span id="totalLucro" class="fw-bold text-success">R$ 0,00</span>
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div style="position:relative;height:220px">
                    <canvas id="graficoFaturamento"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Viagens por Status ── --}}
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-pie-chart me-2 text-primary"></i>
                Viagens por Status
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">

                <div style="width:160px;height:160px">
                    <canvas id="graficoStatus"></canvas>
                </div>

                <div class="mt-3 w-100">
                    @php
                        $statusInfo = [
                            'aberta'            => ['label' => 'Aberta',         'cor' => '#3b82f6'],
                            'em_andamento'      => ['label' => 'Em Andamento',   'cor' => '#f59e0b'],
                            'aguardando_acerto' => ['label' => 'Aguard. Acerto', 'cor' => '#8b5cf6'],
                            'encerrada'         => ['label' => 'Encerrada',      'cor' => '#10b981'],
                        ];
                    @endphp

                    @foreach($statusInfo as $key => $info)
                        @php $total = $viagensPorStatus[$key] ?? 0; @endphp
                        <div class="d-flex align-items-center justify-content-between py-1"
                             style="border-bottom:1px solid #f0f0f0">
                            <div class="d-flex align-items-center gap-2">
                                <span style="display:inline-block;width:12px;height:12px;
                                             border-radius:3px;background:{{ $info['cor'] }};
                                             flex-shrink:0"></span>
                                <span style="font-size:.8rem">{{ $info['label'] }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold" style="font-size:.85rem">{{ $total }}</span>
                                @if($viagensPorStatus->sum() > 0)
                                <span class="text-muted" style="font-size:.75rem;min-width:35px;text-align:right">
                                    {{ number_format(($total / $viagensPorStatus->sum()) * 100, 0) }}%
                                </span>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <div class="d-flex justify-content-between pt-2 fw-bold">
                        <span style="font-size:.8rem">Total</span>
                        <span style="font-size:.85rem">{{ $viagensPorStatus->sum() }}</span>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

<div class="row g-4">

    {{-- ── Últimas Viagens Abertas ── --}}
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between">
                <span><i class="bi bi-truck me-2 text-primary"></i>Viagens em Aberto</span>
                <a href="{{ route('viagens.index') }}" class="btn btn-sm btn-outline-primary">
                    Ver todas
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Motorista</th>
                            <th>Rota</th>
                            <th>Frete</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ultimasViagens as $viagem)
                        <tr onclick="window.location='{{ route('viagens.show', $viagem) }}'"
                            style="cursor:pointer">
                            <td class="ps-3 fw-semibold">{{ $viagem->motorista->nome }}</td>
                            <td class="small">{{ $viagem->origem }} → {{ $viagem->destino }}</td>
                            <td>R$ {{ number_format($viagem->valor_frete, 2, ',', '.') }}</td>
                            <td>
                                <span class="badge badge-status-{{ $viagem->status }}">
                                    {{ ucfirst(str_replace('_', ' ', $viagem->status)) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="bi bi-truck fs-3 d-block mb-2"></i>
                                Nenhuma viagem em aberto.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Top Motoristas do Mês ── --}}
    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-trophy me-2 text-warning"></i>
                Top Motoristas do Mês
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Motorista</th>
                            <th>Viagens</th>
                            <th>Frete Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topMotoristas as $i => $item)
                        <tr>
                            <td class="ps-3">
                                @if($i === 0) 🥇
                                @elseif($i === 1) 🥈
                                @elseif($i === 2) 🥉
                                @else {{ $i + 1 }}
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $item->motorista->nome }}</td>
                            <td>{{ $item->total_viagens }}</td>
                            <td>R$ {{ number_format($item->total_frete, 2, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                Nenhuma viagem encerrada este mês.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // ── Status ──
    const statusLabels = @json($viagensPorStatus->keys());
    const statusData   = @json($viagensPorStatus->values());

    const coresStatus = {
        'aberta'            : '#3b82f6',
        'em_andamento'      : '#f59e0b',
        'aguardando_acerto' : '#8b5cf6',
        'encerrada'         : '#10b981',
    };

    new Chart(document.getElementById('graficoStatus'), {
        type: 'doughnut',
        data: {
            labels: statusLabels.map(s => s.replace(/_/g, ' ')),
            datasets: [{
                data: statusData,
                backgroundColor: statusLabels.map(s => coresStatus[s] || '#ccc'),
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });

    // ── Gráfico de Faturamento com filtro AJAX ──
    let graficoFaturamento = null;

    function formatBRL(valor) {
        return 'R$ ' + parseFloat(valor).toLocaleString('pt-BR', {
            minimumFractionDigits: 2
        });
    }

    function carregarGrafico(periodo, inicio = null, fim = null) {
        let url = `/dashboard/grafico?tipo=${periodo}`;
        if (periodo === 'personalizado' && inicio && fim) {
            url += `&inicio=${inicio}&fim=${fim}`;
        }

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {

            document.getElementById('totalFrete').textContent = formatBRL(data.totais.frete);
            document.getElementById('totalLucro').textContent = formatBRL(data.totais.lucro);

            if (graficoFaturamento) {
                graficoFaturamento.data.labels           = data.labels;
                graficoFaturamento.data.datasets[0].data = data.fretes;
                graficoFaturamento.data.datasets[1].data = data.lucros;
                graficoFaturamento.update();
            } else {
                graficoFaturamento = new Chart(
                    document.getElementById('graficoFaturamento'), {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [
                            {
                                label: 'Faturamento (R$)',
                                data: data.fretes,
                                backgroundColor: 'rgba(249,115,22,0.8)',
                                borderRadius: 6,
                            },
                            {
                                label: 'Lucro (R$)',
                                data: data.lucros,
                                backgroundColor: 'rgba(16,185,129,0.8)',
                                borderRadius: 6,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'top' } },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: v => 'R$ ' + v.toLocaleString('pt-BR')
                                }
                            }
                        }
                    }
                });
            }
        });
    }

    // ── Botões de período ──
    document.querySelectorAll('#btnsPeriodo button').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#btnsPeriodo button').forEach(b => {
                b.classList.remove('btn-primary', 'active');
                b.classList.add('btn-outline-secondary');
            });
            this.classList.remove('btn-outline-secondary');
            this.classList.add('btn-primary', 'active');

            const periodo = this.dataset.periodo;
            const personalizado = document.getElementById('periodoPersonalizado');

            if (periodo === 'personalizado') {
                personalizado.classList.remove('d-none');
            } else {
                personalizado.classList.add('d-none');
                carregarGrafico(periodo);
            }
        });
    });

    // ── Botão aplicar personalizado ──
    document.getElementById('btnAplicar').addEventListener('click', function () {
        const inicio = document.getElementById('dataInicio').value;
        const fim    = document.getElementById('dataFim').value;
        if (!inicio || !fim) {
            alert('Preencha as duas datas.');
            return;
        }
        if (inicio > fim) {
            alert('A data início deve ser anterior à data fim.');
            return;
        }
        carregarGrafico('personalizado', inicio, fim);
    });

    // ── Carrega ao iniciar com 30 dias ──
    carregarGrafico('30');
</script>
@endpush