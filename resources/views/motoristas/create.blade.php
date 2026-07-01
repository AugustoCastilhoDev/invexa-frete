@extends('layouts.app')
@section('title', 'Novo Motorista')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Novo Motorista</h4>
        <small class="text-muted">Preencha os dados do motorista</small>
    </div>
    <a href="{{ route('motoristas.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body p-4">
        <form action="{{ route('motoristas.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nome *</label>
                    <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror"
                           value="{{ old('nome') }}" required>
                    @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">CPF *</label>
                    <input type="text" name="cpf" id="cpf"
                           class="form-control @error('cpf') is-invalid @enderror"
                           value="{{ old('cpf') }}" placeholder="000.000.000-00" required>
                    @error('cpf')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">CNH</label>
                    <input type="text" name="cnh" class="form-control"
                           value="{{ old('cnh') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Categoria CNH</label>
                    <select name="categoria_cnh" class="form-select">
                        <option value="">Selecione</option>
                        @foreach(['A','B','C','D','E','AB','AC','AD','AE'] as $cat)
                            <option value="{{ $cat }}" {{ old('categoria_cnh') == $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Validade CNH</label>
                    <input type="date" name="validade_cnh" class="form-control"
                           value="{{ old('validade_cnh') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Telefone</label>
                    <input type="text" name="telefone" id="telefone" class="form-control"
                           value="{{ old('telefone') }}" placeholder="(00) 00000-0000">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">E-mail</label>
                    <input type="email" name="email" class="form-control"
                           value="{{ old('email') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">% Comissão sobre Frete *</label>
                    <div class="input-group">
                        <input type="number" name="percentual_comissao" step="0.01" min="0" max="100"
                               class="form-control @error('percentual_comissao') is-invalid @enderror"
                               value="{{ old('percentual_comissao', 0) }}" required>
                        <span class="input-group-text">%</span>
                    </div>
                    @error('percentual_comissao')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="ativo" {{ old('status') == 'ativo' ? 'selected' : '' }}>Ativo</option>
                        <option value="inativo" {{ old('status') == 'inativo' ? 'selected' : '' }}>Inativo</option>
                    </select>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-lg me-1"></i> Salvar Motorista
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Máscara de CPF
    document.getElementById('cpf').addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '');
        v = v.replace(/(\d{3})(\d)/, '$1.$2');
        v = v.replace(/(\d{3})(\d)/, '$1.$2');
        v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        this.value = v;
    });

    // Máscara de Telefone
    document.getElementById('telefone').addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '');
        v = v.replace(/^(\d{2})(\d)/, '($1) $2');
        v = v.replace(/(\d{5})(\d{4})$/, '$1-$2');
        this.value = v;
    });
</script>
@endpush