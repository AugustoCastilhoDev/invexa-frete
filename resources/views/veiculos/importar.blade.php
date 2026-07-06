@extends('layouts.app')
@section('title', 'Importar Veículos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Importar Veículos</h4>
        <small class="text-muted">Cadastre vários veículos de uma vez a partir de uma planilha</small>
    </div>
    <a href="{{ route('veiculos.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body p-4">
        <div class="alert alert-light border">
            <i class="bi bi-info-circle me-1"></i>
            Baixe o modelo, preencha uma linha por veículo e envie o arquivo. Colunas aceitas:
            <code>placa, modelo, marca, ano, tipo, renavam, chassi, validade_documento, capacidade_kg, status</code>.
            Apenas <strong>placa</strong>, <strong>modelo</strong> e <strong>tipo</strong> são obrigatórios
            (tipo: truck, carreta, van, utilitario ou outro).
            O vínculo cavalo/carreta não é importado — pode ser feito depois na edição do veículo.
            <a href="{{ route('veiculos.importar.modelo') }}" class="d-block mt-2">
                <i class="bi bi-download me-1"></i> Baixar modelo CSV
            </a>
        </div>

        <form action="{{ route('veiculos.importar.store') }}" method="POST" enctype="multipart/form-data">
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
