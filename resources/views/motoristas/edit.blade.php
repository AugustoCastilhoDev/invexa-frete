@extends('layouts.app')
@section('title', 'Editar Motorista')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Editar Motorista</h4>
        <small class="text-muted">{{ $motorista->nome }}</small>
    </div>
    <a href="{{ route('motoristas.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body p-4">
        <form action="{{ route('motoristas.update', $motorista) }}" method="POST">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nome *</label>
                    <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror"
                           value="{{ old('nome', $motorista->nome) }}" required>
                    @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">CPF *</label>
                    <input type="text" name="cpf" class="form-control @error('cpf') is-invalid @enderror"
                           value="{{ old('cpf', $motorista->cpf) }}" required>
                    @error('cpf')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">CNH</label>
                    <input type="text" name="cnh" class="form-control"
                           value="{{ old('cnh', $motorista->cnh) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Categoria CNH</label>
                    <select name="categoria_cnh" class="form-select">
                        <option value="">Selecione</option>
                        @foreach(['A','B','C','D','E','AB','AC','AD','AE'] as $cat)
                            <option value="{{ $cat }}"
                                {{ old('categoria_cnh', $motorista->categoria_cnh) == $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Validade CNH</label>
                    <input type="date" name="validade_cnh" class="form-control"
                           value="{{ old('validade_cnh', $motorista->validade_cnh?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Telefone</label>
                    <input type="text" name="telefone" class="form-control"
                           value="{{ old('telefone', $motorista->telefone) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">E-mail</label>
                    <input type="email" name="email" class="form-control"
                           value="{{ old('email', $motorista->email) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">% Comissão *</label>
                    <div class="input-group">
                        <input type="number" name="percentual_comissao" step="0.01" min="0" max="100"
                               class="form-control"
                               value="{{ old('percentual_comissao', $motorista->percentual_comissao) }}" required>
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="ativo" {{ old('status', $motorista->status) == 'ativo' ? 'selected' : '' }}>Ativo</option>
                        <option value="inativo" {{ old('status', $motorista->status) == 'inativo' ? 'selected' : '' }}>Inativo</option>
                    </select>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-lg me-1"></i> Atualizar Motorista
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-phone me-2 text-primary"></i>Acesso ao Portal do Motorista
    </div>
    <div class="card-body">
        <p class="text-muted small">
            Permite que {{ $motorista->nome }} acesse suas próprias viagens e acertos, usando o CPF como login.
        </p>

        @if($motorista->portal_ativo)
            <span class="badge bg-success mb-3"><i class="bi bi-check-circle me-1"></i>Acesso ativo</span>

            <form action="{{ route('motoristas.portal.destroy', $motorista) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Desativar o acesso ao portal deste motorista?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger mb-3">
                    <i class="bi bi-x-circle me-1"></i> Desativar Acesso
                </button>
            </form>
        @else
            <span class="badge bg-secondary mb-3"><i class="bi bi-slash-circle me-1"></i>Sem acesso</span>
        @endif

        <form action="{{ route('motoristas.portal.store', $motorista) }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ $motorista->portal_ativo ? 'Nova Senha' : 'Senha de Acesso' }} *</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Confirmar Senha *</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-key me-1"></i>
                        {{ $motorista->portal_ativo ? 'Definir Nova Senha' : 'Ativar Acesso e Definir Senha' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection