@extends('layouts.app')
@section('title', 'Importar Motoristas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Importar Motoristas</h4>
        <small class="text-muted">Cadastre vários motoristas de uma vez a partir de uma planilha</small>
    </div>
    <a href="{{ route('motoristas.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body p-4">
        <div class="alert alert-light border">
            <i class="bi bi-info-circle me-1"></i>
            Baixe o modelo, preencha uma linha por motorista e envie o arquivo. Colunas aceitas:
            <code>nome, cpf, cnh, categoria_cnh, validade_cnh, telefone, email, percentual_comissao, status</code>.
            Apenas <strong>nome</strong>, <strong>cpf</strong> e <strong>percentual_comissao</strong> são obrigatórios.
            <a href="{{ route('motoristas.importar.modelo') }}" class="d-block mt-2">
                <i class="bi bi-download me-1"></i> Baixar modelo CSV
            </a>
        </div>

        <form action="{{ route('motoristas.importar.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Arquivo CSV *</label>
                <input type="file" name="arquivo" accept=".csv,text/csv" class="form-control @error('arquivo') is-invalid @enderror" required>
                @error('arquivo')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-upload me-1"></i> Importar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
