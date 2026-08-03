@extends('layouts.app')
@section('title', 'Meu Perfil')

@section('content')
<div class="mb-4">
    <h4 class="mb-0">Meu Perfil</h4>
    <small class="text-muted">Gerencie suas informações de conta e senha</small>
</div>

@if (session('aviso_2fa'))
<div class="alert alert-warning d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-shield-exclamation fs-5"></i>
    <div>Sua conta exige autenticação em dois fatores (2FA). Configure abaixo antes de continuar usando o sistema.</div>
</div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-body p-4">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body p-4">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body p-4">
                @include('profile.partials.two-factor-authentication-form')
            </div>
        </div>

        @if(auth()->user()?->isAdmin())
        <div class="card border-danger-subtle">
            <div class="card-body p-4">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
