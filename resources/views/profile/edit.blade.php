@extends('layouts.app')
@section('title', 'Meu Perfil')

@section('content')
<div class="mb-4">
    <h4 class="mb-0">Meu Perfil</h4>
    <small class="text-muted">Gerencie suas informações de conta e senha</small>
</div>

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

        <div class="card border-danger-subtle">
            <div class="card-body p-4">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</div>
@endsection
