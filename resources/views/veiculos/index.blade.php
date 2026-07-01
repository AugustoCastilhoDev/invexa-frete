@extends('layouts.app')
@section('title', 'Veículos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Veículos</h4>
        <small class="text-muted">Gerencie os veículos cadastrados</small>
    </div>
    <a href="{{ route('veiculos.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Novo Veículo
    </a>
</div>

{{-- Busca --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('veiculos.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-10">
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="busca" class="form-control border-start-0"
                               placeholder="Buscar por placa, modelo ou marca..."
                               value="{{ $busca ?? '' }}" autofocus>
                    </div>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        Buscar
                    </button>
                </div>
                <div class="col-md-1">
                    <a href="{{ route('veiculos.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Resultado --}}
@if($busca)
<div class="alert alert-info py-2 mb-3">
    <i class="bi bi-funnel me-1"></i>
    Resultado para <strong>"{{ $busca }}"</strong> —
    <strong>{{ $veiculos->total() }}</strong> veículo(s) encontrado(s).
    <a href="{{ route('veiculos.index') }}" class="ms-2 text-decoration-none">Limpar busca</a>
</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Placa</th>
                    <th>Modelo / Marca</th>
                    <th>Tipo</th>
                    <th>Ano</th>
                    <th>Capacidade</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($veiculos as $veiculo)
                <tr>
                    <td class="ps-4 fw-semibold">{{ $veiculo->placa }}</td>
                    <td>{{ $veiculo->modelo }}{{ $veiculo->marca ? ' / '.$veiculo->marca : '' }}</td>
                    <td>{{ ucfirst($veiculo->tipo) }}</td>
                    <td>{{ $veiculo->ano ?? '-' }}</td>
                    <td>{{ $veiculo->capacidade_kg ? number_format($veiculo->capacidade_kg, 0, ',', '.').' kg' : '-' }}</td>
                    <td>
                        @php
                            $badge = match($veiculo->status) {
                                'ativo'      => 'bg-success',
                                'inativo'    => 'bg-secondary',
                                'manutencao' => 'bg-warning text-dark',
                            };
                        @endphp
                        <span class="badge {{ $badge }}">{{ ucfirst($veiculo->status) }}</span>
                    </td>
                    <td class="text-end pe-4">
                        <a href="{{ route('veiculos.show', $veiculo) }}"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('veiculos.edit', $veiculo) }}"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('veiculos.destroy', $veiculo) }}"
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
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="bi bi-car-front fs-3 d-block mb-2"></i>
                        {{ $busca ? 'Nenhum veículo encontrado para "'.$busca.'".' : 'Nenhum veículo cadastrado.' }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($veiculos->hasPages())
    <div class="card-footer">{{ $veiculos->links() }}</div>
    @endif
</div>
@endsection