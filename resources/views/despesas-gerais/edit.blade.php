@extends('layouts.app')
@section('title', 'Editar Despesa')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Editar Despesa Geral</h4>
        <small class="text-muted">{{ $despesa->descricao }}</small>
    </div>
    <a href="{{ route('despesas-gerais.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body p-4">
        <form action="{{ route('despesas-gerais.update', $despesa) }}" method="POST">
            @csrf @method('PUT')
            @include('despesas-gerais._form')
            <div class="col-12 text-end mt-3">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check-lg me-1"></i> Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
