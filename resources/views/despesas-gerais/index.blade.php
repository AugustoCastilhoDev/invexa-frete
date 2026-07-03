@extends('layouts.app')
@section('title', 'Despesas Gerais')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Despesas Gerais</h4>
        <small class="text-muted">Custos administrativos não ligados a uma viagem específica (aluguel, salários, contas...)</small>
    </div>
    <a href="{{ route('despesas-gerais.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Nova Despesa
    </a>
</div>

{{-- Filtros --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('despesas-gerais.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">De</label>
                    <input type="date" name="data_inicio" class="form-control" value="{{ $dataInicio }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Até</label>
                    <input type="date" name="data_fim" class="form-control" value="{{ $dataFim }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Categoria</label>
                    <select name="categoria" class="form-select">
                        <option value="">Todas</option>
                        @foreach(['aluguel','salarios','contas','seguro','impostos','marketing','outros'] as $cat)
                        <option value="{{ $cat }}" {{ $categoria == $cat ? 'selected' : '' }}>
                            {{ ucfirst($cat) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                </div>
                <div class="col-md-1">
                    <a href="{{ route('despesas-gerais.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="alert alert-secondary py-2 mb-3 d-flex justify-content-between align-items-center">
    <span><i class="bi bi-calculator me-1"></i> Total no período</span>
    <strong>R$ {{ number_format($total, 2, ',', '.') }}</strong>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Data</th>
                    <th>Categoria</th>
                    <th>Descrição</th>
                    <th>Valor</th>
                    <th>Recorrente</th>
                    <th class="text-end pe-4">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($despesas as $despesa)
                <tr>
                    <td class="ps-4">{{ $despesa->data_despesa->format('d/m/Y') }}</td>
                    <td>{{ $despesa->categoria_formatada }}</td>
                    <td>{{ $despesa->descricao }}</td>
                    <td>R$ {{ number_format($despesa->valor, 2, ',', '.') }}</td>
                    <td>
                        @if($despesa->recorrente)
                        <span class="badge bg-info-subtle text-info-emphasis">Recorrente</span>
                        @else
                        <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-end pe-4">
                        <a href="{{ route('despesas-gerais.edit', $despesa) }}"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('despesas-gerais.destroy', $despesa) }}"
                              method="POST" class="d-inline"
                              onsubmit="return confirm('Confirma exclusão?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="bi bi-receipt fs-3 d-block mb-2"></i>
                        Nenhuma despesa registrada no período.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($despesas->hasPages())
    <div class="card-footer">{{ $despesas->links() }}</div>
    @endif
</div>
@endsection
