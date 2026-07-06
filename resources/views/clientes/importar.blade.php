@extends('layouts.app')
@section('title', 'Importar Clientes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Importar Clientes</h4>
        <small class="text-muted">Cadastre vários clientes de uma vez a partir de uma planilha</small>
    </div>
    <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body p-4">
        <div class="alert alert-light border">
            <i class="bi bi-info-circle me-1"></i>
            Baixe o modelo, preencha uma linha por cliente e envie o arquivo. Colunas aceitas:
            <code>tipo_pessoa, nome, razao_social, cpf_cnpj, email, telefone, celular, cidade, estado, tabela_frete, status</code>.
            Apenas <strong>tipo_pessoa</strong> (fisica ou juridica), <strong>nome</strong> e <strong>cpf_cnpj</strong> são obrigatórios.
            Endereço detalhado (CEP, logradouro etc.) não é importado — pode ser completado depois na edição do cliente.
            <a href="{{ route('clientes.importar.modelo') }}" class="d-block mt-2">
                <i class="bi bi-download me-1"></i> Baixar modelo CSV
            </a>
        </div>

        <form action="{{ route('clientes.importar.store') }}" method="POST" enctype="multipart/form-data">
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
