@extends('layouts.portal')
@section('title', 'Trocar Senha')

@section('content')
<div class="mb-4">
    <h4 class="mb-0">Trocar Senha</h4>
    <small class="text-muted">Defina uma nova senha de acesso ao portal</small>
</div>

<div class="card" style="max-width:480px">
    <div class="card-body p-4">
        <form action="{{ route('portal.senha.update') }}" method="POST">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label fw-semibold">Senha Atual *</label>
                <input type="password" name="senha_atual" class="form-control @error('senha_atual') is-invalid @enderror" required>
                @error('senha_atual')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Nova Senha *</label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Confirmar Nova Senha *</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg me-1"></i> Salvar Nova Senha
            </button>
        </form>
    </div>
</div>
@endsection
