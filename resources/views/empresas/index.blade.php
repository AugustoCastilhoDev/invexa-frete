@extends('layouts.app')
@section('title', 'Empresas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Empresas</h4>
        <small class="text-muted">Empresas clientes cadastradas na plataforma</small>
    </div>
    <a href="{{ route('empresas.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Nova Empresa
    </a>
</div>

{{-- Busca --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('empresas.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-10">
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="busca" class="form-control border-start-0"
                               placeholder="Buscar por nome ou CNPJ..."
                               value="{{ $busca ?? '' }}" autofocus>
                    </div>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        Buscar
                    </button>
                </div>
                <div class="col-md-1">
                    <a href="{{ route('empresas.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

@if($busca)
<div class="alert alert-info py-2 mb-3">
    <i class="bi bi-funnel me-1"></i>
    Resultado para <strong>"{{ $busca }}"</strong> —
    <strong>{{ $empresas->total() }}</strong> empresa(s) encontrada(s).
    <a href="{{ route('empresas.index') }}" class="ms-2 text-decoration-none">Limpar busca</a>
</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Nome</th>
                    <th>CNPJ</th>
                    <th>Usuários</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($empresas as $empresa)
                <tr>
                    <td class="ps-4 fw-semibold">{{ $empresa->nome }}</td>
                    <td>{{ $empresa->cnpj ?? '-' }}</td>
                    <td>{{ $empresa->usuarios_count }}</td>
                    <td>
                        <span class="badge {{ $empresa->status === 'ativo' ? 'bg-success' : 'bg-secondary' }}">
                            {{ ucfirst($empresa->status) }}
                        </span>
                    </td>
                    <td class="text-end pe-4">
                        <a href="{{ route('empresas.show', $empresa) }}"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('empresas.edit', $empresa) }}"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('empresas.toggle-status', $empresa) }}"
                              method="POST" class="d-inline"
                              onsubmit="return confirm('{{ $empresa->status === 'ativo' ? 'Desativar' : 'Reativar' }} esta empresa? {{ $empresa->status === 'ativo' ? 'Os usuários dela não conseguirão mais fazer login.' : '' }}')">
                            @csrf @method('PATCH')
                            <button class="btn btn-sm {{ $empresa->status === 'ativo' ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                <i class="bi {{ $empresa->status === 'ativo' ? 'bi-slash-circle' : 'bi-check-circle' }}"></i>
                            </button>
                        </form>
                        <form action="{{ route('empresas.suporte.iniciar', $empresa) }}"
                              method="POST" class="d-inline"
                              onsubmit="return confirm('Acessar o sistema como administrador de {{ $empresa->nome }}?')">
                            @csrf
                            <button class="btn btn-sm btn-outline-warning">
                                <i class="bi bi-headset"></i> Suporte
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        <i class="bi bi-buildings fs-3 d-block mb-2"></i>
                        {{ $busca ? 'Nenhuma empresa encontrada para "'.$busca.'".' : 'Nenhuma empresa cadastrada.' }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
    @if($empresas->hasPages())
    <div class="card-footer">{{ $empresas->links() }}</div>
    @endif
</div>
@endsection
