@extends('layouts.app')
@section('title', 'Clientes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Clientes</h4>
        <small class="text-muted">Gerencie os clientes cadastrados</small>
    </div>
    <a href="{{ route('clientes.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Novo Cliente
    </a>
</div>

{{-- Busca --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('clientes.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-10">
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="busca" class="form-control border-start-0"
                               placeholder="Buscar por nome, razão social, CNPJ/CPF, cidade ou telefone..."
                               value="{{ $busca ?? '' }}" autofocus>
                    </div>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        Buscar
                    </button>
                </div>
                <div class="col-md-1">
                    <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary w-100">
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
    <strong>{{ $clientes->total() }}</strong> cliente(s) encontrado(s).
    <a href="{{ route('clientes.index') }}" class="ms-2 text-decoration-none">Limpar busca</a>
</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Nome / Razão Social</th>
                    <th>CPF/CNPJ</th>
                    <th>Cidade</th>
                    <th>Telefone</th>
                    <th>Contato</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clientes as $cliente)
                <tr>
                    <td class="ps-4">
                        <div class="fw-semibold">{{ $cliente->nome }}</div>
                        @if($cliente->razao_social)
                            <small class="text-muted">{{ $cliente->razao_social }}</small>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border">
                            {{ $cliente->tipo_pessoa === 'juridica' ? 'PJ' : 'PF' }}
                        </span>
                        @if($cliente->tipo_pessoa === 'fisica')
                            <x-dado-sensivel :mascarado="$cliente->documento_mascarado" :completo="$cliente->documento_formatado" />
                        @else
                            {{ $cliente->documento_formatado }}
                        @endif
                    </td>
                    <td>{{ $cliente->cidade ?? '-' }}{{ $cliente->estado ? '/'.$cliente->estado : '' }}</td>
                    <td>{{ $cliente->telefone ?? $cliente->celular ?? '-' }}</td>
                    <td>{{ $cliente->contato ?? '-' }}</td>
                    <td>
                        <span class="badge {{ $cliente->status === 'ativo' ? 'bg-success' : 'bg-secondary' }}">
                            {{ ucfirst($cliente->status) }}
                        </span>
                    </td>
                    <td class="text-end pe-4">
                        <a href="{{ route('clientes.show', $cliente) }}"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('clientes.edit', $cliente) }}"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @if(auth()->user()?->isAdmin())
                        <form action="{{ route('clientes.destroy', $cliente) }}"
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
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="bi bi-building fs-3 d-block mb-2"></i>
                        {{ $busca ? 'Nenhum cliente encontrado para "'.$busca.'".' : 'Nenhum cliente cadastrado.' }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($clientes->hasPages())
    <div class="card-footer">{{ $clientes->links() }}</div>
    @endif
</div>
@endsection