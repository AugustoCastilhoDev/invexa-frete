@extends('layouts.app')
@section('title', 'Relatório Financeiro')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Relatório Financeiro</h4>
        <small class="text-muted">Análise por período</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('relatorios.csv', request()->query()) }}" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Exportar CSV
        </a>
        <a href="{{ route('relatorios.pdf', request()->query()) }}"
           target="_blank" class="btn btn-outline-dark">
            <i class="bi bi-printer me-1"></i> Exportar PDF
        </a>
    </div>
</div>

{{-- ── Filtros ── --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('relatorios.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Data Início</label>
                    <input type="date" name="data_inicio" class="form-control form-control-sm"
                           value="{{ $dataInicio }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Data Fim</label>
                    <input type="date" name="data_fim" class="form-control form-control-sm"
                           value="{{ $dataFim }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Motorista</label>
                    <select name="motorista_id" class="form-select form-select-sm">
                        <option value="">Todos os motoristas</option>
                        @foreach($motoristas as $m)
                            <option value="{{ $m->id }}"
                                {{ $motoristaSel == $m->id ? 'selected' : '' }}>
                                {{ $m->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Veículo</label>
                    <select name="veiculo_id" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        @foreach($veiculos as $v)
                            <option value="{{ $v->id }}"
                                {{ $veiculoSel == $v->id ? 'selected' : '' }}>
                                {{ $v->placa }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="todos"            {{ $statusSel === 'todos'             ? 'selected' : '' }}>Todos</option>
                        <option value="encerrada"        {{ $statusSel === 'encerrada'         ? 'selected' : '' }}>Encerradas</option>
                        <option value="aberta"           {{ $statusSel === 'aberta'            ? 'selected' : '' }}>Abertas</option>
                        <option value="em_andamento"     {{ $statusSel === 'em_andamento'      ? 'selected' : '' }}>Em Andamento</option>
                        <option value="aguardando_acerto"{{ $statusSel === 'aguardando_acerto' ? 'selected' : '' }}>Aguard. Acerto</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ── Cards Totalizadores ── --}}
<div class="row g-3 mb-4">
    <div class="col-md-2">
        <div class="card text-center border-start border-primary border-3">
            <div class="card-body py-3">
                <div class="text-muted small">Total Viagens</div>
                <div class="fs-3 fw-bold text-primary">{{ $totais['total_viagens'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-start border-3" style="border-color:#f97316!important">
            <div class="card-body py-3">
                <div class="text-muted small">Faturamento</div>
                <div class="fw-bold" style="color:#f97316">
                    R$ {{ number_format($totais['frete'], 2, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-start border-warning border-3">
            <div class="card-body py-3">
                <div class="text-muted small">Comissões</div>
                <div class="fw-bold text-warning">
                    R$ {{ number_format($totais['motoristas'], 2, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-start border-danger border-3">
            <div class="card-body py-3">
                <div class="text-muted small">Despesas</div>
                <div class="fw-bold text-danger">
                    R$ {{ number_format($totais['combustivel'] + $totais['manutencao'], 2, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-start border-success border-3">
            <div class="card-body py-3">
                <div class="text-muted small">Lucro Líquido</div>
                <div class="fw-bold text-success">
                    R$ {{ number_format($totais['lucro'], 2, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-start border-secondary border-3">
            <div class="card-body py-3">
                <div class="text-muted small">Saldo Motoristas</div>
                <div class="fw-bold text-secondary">
                    R$ {{ number_format($totais['saldo_motorista'], 2, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">

    {{-- ── Resumo por Motorista ── --}}
<div class="col-md-5">
    <div class="card h-100 border-start border-primary border-3">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-person-badge me-2 text-primary"></i>Resumo por Motorista
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Motorista</th>
                        <th class="text-center">Viagens</th>
                        <th class="text-end">Frete</th>
                        <th class="text-end pe-3">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($porMotorista as $item)
                    <tr>
                        <td class="ps-3 fw-semibold">{{ $item['nome'] }}</td>
                        <td class="text-center">{{ $item['viagens'] }}</td>
                        <td class="text-end">R$ {{ number_format($item['frete'], 2, ',', '.') }}</td>
                        <td class="text-end pe-3
                            {{ $item['saldo'] >= 0 ? 'text-success' : 'text-danger' }}">
                            R$ {{ number_format($item['saldo'], 2, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-3">
                            Nenhum dado encontrado.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($porMotorista->hasPages())
        <div class="card-footer bg-white py-2">
            {{ $porMotorista->onEachSide(1)->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>

    {{-- ── Gráfico de Despesas ── --}}
    <div class="col-md-7">
        <div class="card h-100 border-start border-3" style="border-color:#8b5cf6!important">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-pie-chart me-2 text-primary"></i>Composição das Despesas
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <div style="width:180px;height:180px">
                    <canvas id="graficoDespesas"></canvas>
                </div>

                {{-- Legenda customizada --}}
                <div class="mt-3 w-100" style="max-width:320px">
                    @php
                        $despesasInfo = [
                            ['label' => 'Combustível',          'cor' => '#f59e0b', 'valor' => $totais['combustivel']],
                            ['label' => 'Manutenção',           'cor' => '#ef4444', 'valor' => $totais['manutencao']],
                            ['label' => 'Comissões Motoristas', 'cor' => '#8b5cf6', 'valor' => $totais['motoristas']],
                            ['label' => 'Lucro Transportadora', 'cor' => '#10b981', 'valor' => $totais['lucro']],
                        ];
                        $totalGeral = collect($despesasInfo)->sum('valor');
                    @endphp

                    @foreach($despesasInfo as $info)
                        <div class="d-flex align-items-center justify-content-between py-1"
                             style="border-bottom:1px solid #f0f0f0">
                            <div class="d-flex align-items-center gap-2">
                                <span style="display:inline-block;width:12px;height:12px;
                                             border-radius:3px;background:{{ $info['cor'] }};
                                             flex-shrink:0"></span>
                                <span style="font-size:.8rem">{{ $info['label'] }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold" style="font-size:.85rem">
                                    R$ {{ number_format($info['valor'], 2, ',', '.') }}
                                </span>
                                @if($totalGeral > 0)
                                <span class="text-muted" style="font-size:.75rem;min-width:35px;text-align:right">
                                    {{ number_format(($info['valor'] / $totalGeral) * 100, 0) }}%
                                </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ── Listagem Detalhada ── --}}
<div class="card border-start border-secondary border-3">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-list-ul me-2 text-primary"></i>
        Detalhamento das Viagens
        <span class="badge bg-secondary ms-2">{{ $totais['total_viagens'] }}</span>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">#</th>
                    <th>Motorista</th>
                    <th>Veículo</th>
                    <th>Rota</th>
                    <th>Saída</th>
                    <th class="text-end">Frete</th>
                    <th class="text-end">Combustível</th>
                    <th class="text-end">Manutenção</th>
                    <th class="text-end">Comissão</th>
                    <th class="text-end">Lucro</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($viagens as $viagem)
                <tr onclick="window.location='{{ route('viagens.show', $viagem) }}'"
                    style="cursor:pointer">
                    <td class="ps-3 text-muted">#{{ $viagem->id }}</td>
                    <td>{{ $viagem->motorista->nome }}</td>
                    <td>{{ $viagem->veiculo->placa }}</td>
                    <td class="small">{{ $viagem->origem }} → {{ $viagem->destino }}</td>
                    <td>{{ $viagem->data_saida->format('d/m/Y') }}</td>
                    <td class="text-end">R$ {{ number_format($viagem->valor_frete, 2, ',', '.') }}</td>
                    <td class="text-end text-warning">R$ {{ number_format($viagem->total_combustivel, 2, ',', '.') }}</td>
                    <td class="text-end text-danger">R$ {{ number_format($viagem->total_manutencao, 2, ',', '.') }}</td>
                    <td class="text-end">R$ {{ number_format($viagem->valor_motorista, 2, ',', '.') }}</td>
                    <td class="text-end fw-semibold {{ $viagem->lucro_transportadora >= 0 ? 'text-success' : 'text-danger' }}">
                        R$ {{ number_format($viagem->lucro_transportadora, 2, ',', '.') }}
                    </td>
                    <td>
                        <span class="badge badge-status-{{ $viagem->status }}">
                            {{ ucfirst(str_replace('_', ' ', $viagem->status)) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="text-center text-muted py-4">
                        Nenhuma viagem encontrada no período selecionado.
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($viagens->count() > 0)
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="5" class="ps-3">Totais</td>
                    <td class="text-end">R$ {{ number_format($totais['frete'], 2, ',', '.') }}</td>
                    <td class="text-end text-warning">R$ {{ number_format($totais['combustivel'], 2, ',', '.') }}</td>
                    <td class="text-end text-danger">R$ {{ number_format($totais['manutencao'], 2, ',', '.') }}</td>
                    <td class="text-end">R$ {{ number_format($totais['motoristas'], 2, ',', '.') }}</td>
                    <td class="text-end {{ $totais['lucro'] >= 0 ? 'text-success' : 'text-danger' }}">
                        R$ {{ number_format($totais['lucro'], 2, ',', '.') }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('graficoDespesas'), {
        type: 'doughnut',
        data: {
            labels: ['Combustível', 'Manutenção', 'Comissões Motoristas', 'Lucro Transportadora'],
            datasets: [{
                data: [
                    {{ $totais['combustivel'] }},
                    {{ $totais['manutencao'] }},
                    {{ $totais['motoristas'] }},
                    {{ $totais['lucro'] }},
                ],
                backgroundColor: [
                    '#f59e0b',
                    '#ef4444',
                    '#8b5cf6',
                    '#10b981',
                ],
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            }
        }
    });
</script>
@endpush