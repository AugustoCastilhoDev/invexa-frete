@extends('layouts.app')
@section('title', 'Nova Empresa')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Nova Empresa</h4>
        <small class="text-muted">Cadastre a empresa e o administrador inicial dela</small>
    </div>
    <a href="{{ route('empresas.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body p-4">
        <form action="{{ route('empresas.store') }}" method="POST">
            @csrf
            <h6 class="fw-semibold mb-3">Dados da empresa</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nome *</label>
                    <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror"
                           value="{{ old('nome') }}" required>
                    @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">CNPJ</label>
                    <input type="text" name="cnpj" class="form-control @error('cnpj') is-invalid @enderror"
                           value="{{ old('cnpj') }}">
                    @error('cnpj')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <h6 class="fw-semibold mb-3">Administrador inicial</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nome *</label>
                    <input type="text" name="admin_name" class="form-control @error('admin_name') is-invalid @enderror"
                           value="{{ old('admin_name') }}" required>
                    @error('admin_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">E-mail *</label>
                    <input type="email" name="admin_email" class="form-control @error('admin_email') is-invalid @enderror"
                           value="{{ old('admin_email') }}" required>
                    @error('admin_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Senha *</label>
                    <input type="password" name="admin_password"
                           class="form-control @error('admin_password') is-invalid @enderror" required>
                    @error('admin_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Confirmar senha *</label>
                    <input type="password" name="admin_password_confirmation" class="form-control" required>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-lg me-1"></i> Salvar Empresa
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
