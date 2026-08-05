@extends('layouts.app')
@section('title', 'Página Inicial')

@section('content')

{{-- ── Cards de Operação ── --}}
<div class="row g-3 mb-3">

    <div class="col-md-3 col-lg-3">
        <div class="card h-100 border-start border-primary border-3 card-accent-blue">
            <div class="card-body">
                <div class="text-muted small mb-1">Em Aberto</div>
                <div class="fs-3 fw-bold text-primary">{{ $totalViagensEmAberto }}</div>
                <div class="text-muted" style="font-size:.75rem">programada, aguardando carregar/iniciar</div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-lg-3">
        <div class="card h-100 border-start border-3 card-accent-orange" style="border-color:#f97316!important">
            <div class="card-body">
                <div class="text-muted small mb-1">Viagem Iniciada</div>
                <div class="fs-3 fw-bold" style="color:#f97316">{{ $totalViagensIniciadas }}</div>
                <div class="text-muted" style="font-size:.75rem">já em rota</div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-lg-3">
        <div class="card h-100 border-start border-3 card-accent-purple" style="border-color:#8b5cf6!important">
            <div class="card-body">
                <div class="text-muted small mb-1">Aguard. Acerto</div>
                <div class="fs-3 fw-bold" style="color:#8b5cf6">{{ $totalAguardandoAcerto }}</div>
                <div class="text-muted" style="font-size:.75rem">a finalizar</div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-lg-3">
        <div class="card h-100 border-start border-success border-3 card-accent-green">
            <div class="card-body">
                <div class="text-muted small mb-1">Encerradas no Mês</div>
                <div class="fs-3 fw-bold text-success">{{ $totalViagensEncerradasMes }}</div>
                <div class="text-muted" style="font-size:.75rem">{{ now()->translatedFormat('F') }}</div>
            </div>
        </div>
    </div>

</div>

{{-- ── Cards de Frota e Programação ── --}}
<div class="row g-3 mb-4">

    <div class="col-md-3 col-lg-3">
        <div class="card h-100 border-start border-secondary border-3 card-accent-gray">
            <div class="card-body">
                <div class="text-muted small mb-1">Frota / Motoristas</div>
                <div class="fs-3 fw-bold text-secondary">
                    {{ $totalVeiculosAtivos }}<span class="fs-6 text-muted">/{{ $totalMotoristasAtivos }}</span>
                </div>
                <div class="text-muted" style="font-size:.75rem">veículos / motoristas</div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-lg-3">
        <div class="card h-100 border-start border-primary border-3 card-accent-blue">
            <div class="card-body">
                <div class="text-muted small mb-1">Veículos Programados</div>
                <div class="fs-3 fw-bold text-primary">{{ $totalVeiculosProgramados }}</div>
                <div class="text-muted" style="font-size:.75rem">próxima viagem já planejada</div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-lg-3">
        <div class="card h-100 border-start border-warning border-3">
            <div class="card-body">
                <div class="d-flex align-items-center gap-1 mb-1">
                    <i class="bi bi-exclamation-triangle text-warning" style="font-size:.8rem"></i>
                    <span class="text-muted small">Sem Próxima Viagem</span>
                </div>
                <div class="fs-3 fw-bold text-warning">{{ $totalVeiculosSemProgramacao }}</div>
                <div class="text-muted" style="font-size:.75rem">veículo ativo sem programação</div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-lg-3">
        <div class="card h-100 border-start border-danger border-3">
            <div class="card-body">
                <div class="d-flex align-items-center gap-1 mb-1">
                    <i class="bi bi-clock-history text-danger" style="font-size:.8rem"></i>
                    <span class="text-muted small">Risco de No-Show</span>
                </div>
                <div class="fs-3 fw-bold text-danger">{{ $totalRiscoNoShow }}</div>
                <div class="text-muted" style="font-size:.75rem">coleta em ≤2h sem chegada informada</div>
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

    {{-- ── Viagens por Status ── --}}
    <div class="col-md-6">
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
</script>
@endpush