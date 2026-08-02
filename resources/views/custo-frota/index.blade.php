@extends('layouts.app')
@section('title', 'Custo da Frota')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Custo da Frota</h4>
        <small class="text-muted">Custo operacional por km, veículo a veículo, comparado com a receita de frete</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('custo-frota.csv', request()->query()) }}" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Exportar CSV
        </a>
    </div>
</div>

{{-- ── Filtros ── --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('custo-frota.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Data Início</label>
                    <input type="date" name="data_inicio" class="form-control form-control-sm" value="{{ $dataInicio }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Data Fim</label>
                    <input type="date" name="data_fim" class="form-control form-control-sm" value="{{ $dataFim }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ── Cards Totalizadores ── --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-center border-start border-primary border-3">
            <div class="card-body py-3">
                <div class="text-muted small">Custo médio da frota</div>
                <div class="fs-4 fw-bold text-primary">
                    {{ $custoMedioFrota !== null ? 'R$ ' . number_format($custoMedioFrota, 2, ',', '.') . '/km' : '—' }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-start border-warning border-3">
            <div class="card-body py-3">
                <div class="text-muted small">Custo fixo (direto + rateado)</div>
                <div class="fs-4 fw-bold text-warning">
                    {{ number_format($percentualCustoFixo, 1, ',', '.') }}%
                </div>
                <div class="text-muted" style="font-size:.75rem">
                    R$ {{ number_format($custoFixoTotal, 2, ',', '.') }} do custo total
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-start border-danger border-3">
            <div class="card-body py-3">
                <div class="text-muted small">Veículo mais caro</div>
                @if($veiculoMaisCaro)
                <div class="fs-4 fw-bold text-danger">R$ {{ number_format($veiculoMaisCaro['custoPorKm'], 2, ',', '.') }}/km</div>
                <div class="text-muted" style="font-size:.75rem">{{ $veiculoMaisCaro['veiculo']->placa }}</div>
                @else
                <div class="fs-4 fw-bold text-muted">—</div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-start border-success border-3">
            <div class="card-body py-3">
                <div class="text-muted small">Veículo mais eficiente</div>
                @if($veiculoMaisEficiente)
                <div class="fs-4 fw-bold text-success">R$ {{ number_format($veiculoMaisEficiente['custoPorKm'], 2, ',', '.') }}/km</div>
                <div class="text-muted" style="font-size:.75rem">{{ $veiculoMaisEficiente['veiculo']->placa }}</div>
                @else
                <div class="fs-4 fw-bold text-muted">—</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- ── Ranking por veículo ── --}}
    <div class="col-lg-7">
        <div class="card h-100 border-start border-secondary border-3">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-speedometer2 me-2 text-primary"></i>Ranking de custo por km</span>
                <small class="text-muted fw-normal">mais caro → mais barato</small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Veículo</th>
                            <th>KM no período</th>
                            <th>Custo Total</th>
                            <th>R$/km</th>
                            <th>Receita R$/km</th>
                            <th>Margem/km</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $maiorCustoKm = $linhas->max('custoPorKm') ?: 1; @endphp
                        @forelse($linhas as $linha)
                        <tr>
                            <td class="ps-3 fw-semibold">{{ $linha['veiculo']->placa }}</td>
                            <td>{{ number_format($linha['kmRodados'], 0, ',', '.') }} km</td>
                            <td>R$ {{ number_format($linha['custoTotal'], 2, ',', '.') }}</td>
                            <td>
                                @if($linha['custoPorKm'] !== null)
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:50px;height:6px;border-radius:3px;background:var(--bs-tertiary-bg);overflow:hidden">
                                        <div style="height:100%;border-radius:3px;background:#2563eb;width:{{ round(($linha['custoPorKm'] / $maiorCustoKm) * 100) }}%"></div>
                                    </div>
                                    <span class="fw-semibold">{{ number_format($linha['custoPorKm'], 2, ',', '.') }}</span>
                                </div>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $linha['receitaPorKm'] !== null ? number_format($linha['receitaPorKm'], 2, ',', '.') : '—' }}</td>
                            <td>
                                @if($linha['margemPorKm'] !== null)
                                <span class="fw-semibold {{ $linha['margemPorKm'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $linha['margemPorKm'] >= 0 ? '+' : '' }}{{ number_format($linha['margemPorKm'], 2, ',', '.') }}
                                </span>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Nenhum veículo encontrado no período.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Tendência custo x receita por km ── --}}
    <div class="col-lg-5">
        <div class="card h-100 border-start border-secondary border-3">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-graph-up me-2 text-primary"></i>Custo x Receita por km
                <small class="text-muted fw-normal d-block">últimos 6 meses, frota inteira</small>
            </div>
            <div class="card-body">
                <canvas id="graficoTendencia" height="220"></canvas>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const tendencia = @json($tendencia);

    new Chart(document.getElementById('graficoTendencia'), {
        type: 'line',
        data: {
            labels: tendencia.map(m => m.label),
            datasets: [
                {
                    label: 'Custo/km',
                    data: tendencia.map(m => m.custoPorKm),
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37,99,235,0.1)',
                    tension: 0.3,
                    spanGaps: true,
                },
                {
                    label: 'Receita/km',
                    data: tendencia.map(m => m.receitaPorKm),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16,185,129,0.1)',
                    tension: 0.3,
                    spanGaps: true,
                },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top' } },
            scales: {
                y: {
                    ticks: { callback: v => 'R$ ' + v.toLocaleString('pt-BR') }
                }
            }
        }
    });
</script>
@endpush
