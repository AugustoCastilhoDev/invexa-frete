@extends('layouts.app')
@section('title', 'Histórico de Manutenções')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Histórico de Manutenções</h4>
        <small class="text-muted">Todas as manutenções da frota, em um só lugar</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('manutencoes.csv', request()->query()) }}" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Exportar CSV
        </a>
    </div>
</div>

{{-- ── Filtros ── --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('manutencoes.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Veículo</label>
                    <select name="veiculo_id" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        @foreach($veiculos as $v)
                            <option value="{{ $v->id }}"
                                {{ request('veiculo_id') == $v->id ? 'selected' : '' }}>
                                {{ $v->placa }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Tipo</label>
                    <select name="tipo" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="preventiva" {{ request('tipo') === 'preventiva' ? 'selected' : '' }}>Preventiva</option>
                        <option value="corretiva" {{ request('tipo') === 'corretiva' ? 'selected' : '' }}>Corretiva</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="concluida" {{ request('status') === 'concluida' ? 'selected' : '' }}>Concluída</option>
                        <option value="em_andamento" {{ request('status') === 'em_andamento' ? 'selected' : '' }}>Em andamento</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Data Início</label>
                    <input type="date" name="data_inicio" class="form-control form-control-sm"
                           value="{{ request('data_inicio') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Data Fim</label>
                    <input type="date" name="data_fim" class="form-control form-control-sm"
                           value="{{ request('data_fim') }}">
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
                <div class="text-muted small">Registros</div>
                <div class="fs-3 fw-bold text-primary">{{ $totalRegistros }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-start border-warning border-3">
            <div class="card-body py-3">
                <div class="text-muted small">Total Gasto</div>
                <div class="fw-bold text-warning">
                    R$ {{ number_format($totalGasto, 2, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Listagem ── --}}
<div class="card border-start border-secondary border-3">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-tools me-2 text-warning"></i>Manutenções
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Veículo</th>
                    <th>Tipo</th>
                    <th>Descrição</th>
                    <th>Data</th>
                    <th>KM</th>
                    <th>Valor</th>
                    <th>Próxima</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($manutencoes as $manutencao)
                <tr onclick="window.location='{{ route('veiculos.show', $manutencao->veiculo) }}'"
                    style="cursor:pointer">
                    <td class="ps-3 fw-semibold">{{ $manutencao->veiculo->placa }}</td>
                    <td>
                        <span class="badge {{ $manutencao->tipo === 'preventiva' ? 'bg-info text-dark' : 'bg-danger' }}">
                            {{ ucfirst($manutencao->tipo) }}
                        </span>
                    </td>
                    <td>{{ $manutencao->descricao }}</td>
                    <td>{{ $manutencao->data_manutencao->format('d/m/Y') }}</td>
                    <td>{{ $manutencao->km_veiculo ? number_format($manutencao->km_veiculo, 0, ',', '.') : '-' }}</td>
                    <td>R$ {{ number_format($manutencao->valor, 2, ',', '.') }}</td>
                    <td class="small text-muted">
                        @if($manutencao->proxima_manutencao_data)
                            {{ $manutencao->proxima_manutencao_data->format('d/m/Y') }}
                        @elseif($manutencao->proxima_manutencao_km)
                            {{ number_format($manutencao->proxima_manutencao_km, 0, ',', '.') }} km
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($manutencao->status === 'em_andamento')
                            <span class="badge bg-warning text-dark">Em andamento</span>
                        @else
                            <span class="badge bg-success">Concluída</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        Nenhuma manutenção encontrada.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
    @if($manutencoes->hasPages())
    <div class="card-footer bg-white py-2">
        {{ $manutencoes->onEachSide(1)->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

@endsection
