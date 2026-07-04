@extends('layouts.app')
@section('title', 'Editar Empresa')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Editar Empresa</h4>
        <small class="text-muted">{{ $empresa->nome }}</small>
    </div>
    <a href="{{ route('empresas.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body p-4">
        <form action="{{ route('empresas.update', $empresa) }}" method="POST">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nome *</label>
                    <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror"
                           value="{{ old('nome', $empresa->nome) }}" required>
                    @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">CNPJ</label>
                    <input type="text" name="cnpj" class="form-control @error('cnpj') is-invalid @enderror"
                           value="{{ old('cnpj', $empresa->cnpj) }}">
                    @error('cnpj')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-lg me-1"></i> Salvar Alterações
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
