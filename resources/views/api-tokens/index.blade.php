@extends('layouts.app')
@section('title', 'Tokens de API')

@section('content')
<div class="mb-4">
    <h4 class="mb-0">Tokens de API</h4>
    <small class="text-muted">Gere um token para integrar outros sistemas com o Invexa Frete pela API REST</small>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('token_gerado'))
    <div class="alert alert-warning">
        <strong><i class="bi bi-key me-1"></i> Copie seu token agora — ele não será exibido novamente:</strong>
        <div class="mt-2">
            <code class="user-select-all d-block p-2 bg-body-secondary rounded" style="word-break:break-all">{{ session('token_gerado') }}</code>
        </div>
        <small class="text-muted d-block mt-2">
            Envie no cabeçalho <code>Authorization: Bearer {{ Str::limit(session('token_gerado'), 20) }}...</code> de cada requisição à API.
        </small>
    </div>
@endif

<div class="card border-start border-primary border-3 mb-4">
    <div class="card-header bg-transparent">
        <i class="bi bi-plus-lg me-1"></i> Novo token
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('api-tokens.store') }}" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-8">
                <label class="form-label small text-muted mb-1">Nome (identifica onde esse token está sendo usado)</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       placeholder="Ex.: Integração ERP, App parceiro..." value="{{ old('name') }}" required maxlength="100">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-key me-1"></i> Gerar token
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card border-start border-secondary border-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Nome</th>
                        <th>Criado em</th>
                        <th>Último uso</th>
                        <th class="text-end pe-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tokens as $token)
                    <tr>
                        <td class="ps-4 fw-semibold">{{ $token->name }}</td>
                        <td>{{ $token->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $token->last_used_at?->format('d/m/Y H:i') ?? 'Nunca usado' }}</td>
                        <td class="text-end pe-4">
                            <form action="{{ route('api-tokens.destroy', $token->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Revogar este token? Qualquer integração usando ele para de funcionar imediatamente.')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i> Revogar
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            <i class="bi bi-key fs-3 d-block mb-2"></i>
                            Nenhum token gerado ainda.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
