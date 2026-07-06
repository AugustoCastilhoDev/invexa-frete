@extends('layouts.app')
@section('title', 'Motoristas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Motoristas</h4>
        <small class="text-muted">Gerencie os motoristas cadastrados</small>
    </div>
    <a href="{{ route('motoristas.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Novo Motorista
    </a>
</div>

{{-- Busca --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('motoristas.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-10">
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="busca" class="form-control border-start-0"
                               placeholder="Buscar por nome, CPF ou telefone..."
                               value="{{ $busca ?? '' }}" autofocus>
                    </div>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        Buscar
                    </button>
                </div>
                <div class="col-md-1">
                    <a href="{{ route('motoristas.index') }}" class="btn btn-outline-secondary w-100">
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
    <strong>{{ $motoristas->total() }}</strong> motorista(s) encontrado(s).
    <a href="{{ route('motoristas.index') }}" class="ms-2 text-decoration-none">Limpar busca</a>
</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Nome</th>
                    <th>CPF</th>
                    <th>Telefone</th>
                    <th>Comissão</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($motoristas as $motorista)
                <tr>
                    <td class="ps-4 fw-semibold">{{ $motorista->nome }}</td>
                    <td><x-dado-sensivel :mascarado="$motorista->cpf_mascarado" :completo="$motorista->cpf" /></td>
                    <td>{{ $motorista->telefone ?? '-' }}</td>
                    <td>{{ number_format($motorista->percentual_comissao, 2, ',', '.') }}%</td>
                    <td>
                        <span class="badge {{ $motorista->status === 'ativo' ? 'bg-success' : 'bg-secondary' }}">
                            {{ ucfirst($motorista->status) }}
                        </span>
                    </td>
                    <td class="text-end pe-4">
                        <a href="{{ route('motoristas.show', $motorista) }}"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('motoristas.edit', $motorista) }}"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @if(auth()->user()?->isAdmin())
                        <form action="{{ route('motoristas.destroy', $motorista) }}"
                              method="POST" class="d-inline"
                              onsubmit="return confirm('Confirma exclusão?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="bi bi-person-badge fs-3 d-block mb-2"></i>
                        {{ $busca ? 'Nenhum motorista encontrado para "'.$busca.'".' : 'Nenhum motorista cadastrado.' }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
    @if($motoristas->hasPages())
    <div class="card-footer">{{ $motoristas->links() }}</div>
    @endif
</div>
@endsection