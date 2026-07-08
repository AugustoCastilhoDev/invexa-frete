@extends('layouts.app')
@section('title', 'Programação de Frota')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Programação de Frota</h4>
        <small class="text-muted">Planeje a próxima viagem de cada motorista/veículo antes de encerrar a atual</small>
    </div>
    <a href="{{ route('programacoes.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Nova Programação
    </a>
</div>

{{-- ── Cards de Controle de Frota ── --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-center border-start border-primary border-3">
            <div class="card-body py-3">
                <div class="text-muted small">Programações Pendentes</div>
                <div class="fs-3 fw-bold text-primary">{{ $totalPendentes }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-start border-warning border-3">
            <div class="card-body py-3">
                <div class="d-flex align-items-center justify-content-center gap-1 mb-1">
                    <i class="bi bi-exclamation-triangle text-warning" style="font-size:.8rem"></i>
                    <span class="text-muted small">Veículos Sem Próxima Viagem</span>
                </div>
                <div class="fw-bold text-warning fs-3">{{ $totalVeiculosSemProgramacao }}</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Filtros ── --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('programacoes.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="pendente"  {{ request('status', 'pendente') === 'pendente'  ? 'selected' : '' }}>Pendente</option>
                        <option value="confirmada"{{ request('status') === 'confirmada'            ? 'selected' : '' }}>Confirmada</option>
                        <option value="todas"     {{ request('status') === 'todas'                 ? 'selected' : '' }}>Todas</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Motorista</label>
                    <select name="motorista_id" class="form-select form-select-sm">
                        <option value="">Todos os motoristas</option>
                        @foreach($motoristas as $m)
                            <option value="{{ $m->id }}" {{ request('motorista_id') == $m->id ? 'selected' : '' }}>
                                {{ $m->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Veículo</label>
                    <select name="veiculo_id" class="form-select form-select-sm">
                        <option value="">Todos os veículos</option>
                        @foreach($veiculos as $v)
                            <option value="{{ $v->id }}" {{ request('veiculo_id') == $v->id ? 'selected' : '' }}>
                                {{ $v->placa }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-search"></i>
                    </button>
                    <a href="{{ route('programacoes.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ── Tabela ── --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">#</th>
                    <th>Motorista</th>
                    <th>Veículo</th>
                    <th>Cliente</th>
                    <th>Origem / Destino</th>
                    <th>Data Prevista</th>
                    <th>Frete</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($programacoes as $programacao)
                <tr>
                    <td class="ps-4 text-muted">#{{ $programacao->id }}</td>
                    <td class="fw-semibold">{{ $programacao->motorista->nome }}</td>
                    <td>{{ $programacao->veiculo->placa }}</td>
                    <td>{{ $programacao->cliente->nome ?? '-' }}</td>
                    <td>{{ $programacao->origem }} → {{ $programacao->destino }}</td>
                    <td>{{ $programacao->data_prevista->format('d/m/Y') }}</td>
                    <td>{{ $programacao->valor_frete !== null ? 'R$ ' . number_format($programacao->valor_frete, 2, ',', '.') : '-' }}</td>
                    <td>
                        @if($programacao->status === 'pendente')
                            <span class="badge bg-warning-subtle text-warning">Pendente</span>
                        @else
                            <span class="badge bg-success-subtle text-success">Confirmada</span>
                        @endif
                    </td>
                    <td class="text-end pe-4">
                        @if($programacao->estaPendente())
                            <a href="{{ route('viagens.create', [
                                    'programacao_id' => $programacao->id,
                                    'motorista_id'   => $programacao->motorista_id,
                                    'veiculo_id'     => $programacao->veiculo_id,
                                    'cliente_id'     => $programacao->cliente_id,
                                    'origem'         => $programacao->origem,
                                    'destino'        => $programacao->destino,
                                    'valor_frete'    => $programacao->valor_frete,
                                ]) }}"
                               class="btn btn-sm btn-success" title="Confirmar e Abrir Viagem">
                                <i class="bi bi-check-circle me-1"></i> Confirmar
                            </a>
                            <a href="{{ route('programacoes.edit', $programacao) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('programacoes.destroy', $programacao) }}"
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Excluir esta programação?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        @else
                            <a href="{{ route('viagens.show', $programacao->viagem_id) }}"
                               class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye me-1"></i> Ver Viagem
                            </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-5">
                        <i class="bi bi-signpost-2 fs-3 d-block mb-2"></i>
                        Nenhuma programação encontrada.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
    @if($programacoes->hasPages())
    <div class="card-footer">{{ $programacoes->links() }}</div>
    @endif
</div>
@endsection
