@extends('layouts.app')
@section('title', 'Usuários')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Usuários</h4>
        <small class="text-muted">Gerencie quem tem acesso ao sistema</small>
    </div>
    <a href="{{ route('users.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Novo Usuário
    </a>
</div>

{{-- Busca --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('users.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-10">
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="busca" class="form-control border-start-0"
                               placeholder="Buscar por nome ou e-mail..."
                               value="{{ $busca ?? '' }}" autofocus>
                    </div>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        Buscar
                    </button>
                </div>
                <div class="col-md-1">
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary w-100">
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
    <strong>{{ $users->total() }}</strong> usuário(s) encontrado(s).
    <a href="{{ route('users.index') }}" class="ms-2 text-decoration-none">Limpar busca</a>
</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Nome</th>
                    <th>E-mail</th>
                    <th>Papel</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td class="ps-4 fw-semibold">
                        {{ $user->name }}
                        @if($user->id === auth()->id())
                            <span class="badge bg-light text-dark border">você</span>
                        @endif
                    </td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span class="badge {{ $user->role === 'admin' ? 'bg-primary' : 'bg-secondary' }}">
                            {{ $user->role === 'admin' ? 'Administrador' : 'Operador' }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $user->status === 'ativo' ? 'bg-success' : 'bg-secondary' }}">
                            {{ ucfirst($user->status) }}
                        </span>
                    </td>
                    <td class="text-end pe-4">
                        <a href="{{ route('users.edit', $user) }}"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @if($user->id !== auth()->id())
                        <form action="{{ route('users.destroy', $user) }}"
                              method="POST" class="d-inline"
                              onsubmit="return confirm('Confirma desativação deste usuário?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-person-x"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        <i class="bi bi-people fs-3 d-block mb-2"></i>
                        {{ $busca ? 'Nenhum usuário encontrado para "'.$busca.'".' : 'Nenhum usuário cadastrado.' }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="card-footer">{{ $users->links() }}</div>
    @endif
</div>
@endsection
