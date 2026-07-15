@extends('layouts.app')
@section('title', 'Editar Veículo')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Editar Veículo</h4>
        <small class="text-muted">{{ $veiculo->placa }} — {{ $veiculo->modelo }}</small>
    </div>
    <a href="{{ route('veiculos.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body p-4">
        <form action="{{ route('veiculos.update', $veiculo) }}" method="POST">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Placa *</label>
                    <input type="text" name="placa"
                           class="form-control @error('placa') is-invalid @enderror"
                           value="{{ old('placa', $veiculo->placa) }}" required>
                    @error('placa')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Modelo *</label>
                    <input type="text" name="modelo" class="form-control"
                           value="{{ old('modelo', $veiculo->modelo) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Marca</label>
                    <input type="text" name="marca" class="form-control"
                           value="{{ old('marca', $veiculo->marca) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Ano</label>
                    <input type="number" name="ano" class="form-control"
                           value="{{ old('ano', $veiculo->ano) }}"
                           min="1990" max="{{ date('Y') + 1 }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Tipo *</label>
                    <select name="tipo" id="tipo-veiculo" class="form-select" required
                            onchange="document.getElementById('wrapper-cavalo-vinculado').classList.toggle('d-none', this.value !== 'carreta')">
                        @foreach(['truck'=>'Truck','carreta'=>'Carreta','van'=>'Van','utilitario'=>'Utilitário','outro'=>'Outro'] as $val => $label)
                            <option value="{{ $val }}"
                                {{ old('tipo', $veiculo->tipo) == $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 {{ old('tipo', $veiculo->tipo) !== 'carreta' ? 'd-none' : '' }}" id="wrapper-cavalo-vinculado">
                    <label class="form-label fw-semibold">Cavalo Vinculado</label>
                    <select name="cavalo_id" class="form-select">
                        <option value="">— Nenhum (avulsa) —</option>
                        @foreach($cavalos as $cavalo)
                            <option value="{{ $cavalo->id }}"
                                {{ (string) old('cavalo_id', $veiculo->cavalo_id) === (string) $cavalo->id ? 'selected' : '' }}>
                                {{ $cavalo->placa }} — {{ $cavalo->modelo }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">RENAVAM</label>
                    <input type="text" name="renavam" class="form-control"
                           value="{{ old('renavam', $veiculo->renavam) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Chassi</label>
                    <input type="text" name="chassi" class="form-control"
                           value="{{ old('chassi', $veiculo->chassi) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Validade do Documento (CRLV)</label>
                    <input type="date" name="validade_documento" class="form-control"
                           value="{{ old('validade_documento', optional($veiculo->validade_documento)->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Capacidade (kg)</label>
                    <div class="input-group">
                        <input type="number" name="capacidade_kg" class="form-control"
                               value="{{ old('capacidade_kg', $veiculo->capacidade_kg) }}"
                               step="0.01" min="0">
                        <span class="input-group-text">kg</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Tara (kg)</label>
                    <div class="input-group">
                        <input type="number" name="tara_kg" class="form-control"
                               value="{{ old('tara_kg', $veiculo->tara_kg) }}" min="0">
                        <span class="input-group-text">kg</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Status *</label>
                    <select name="status" class="form-select" required>
                        @foreach(['ativo'=>'Ativo','inativo'=>'Inativo','manutencao'=>'Em Manutenção'] as $val => $label)
                            <option value="{{ $val }}"
                                {{ old('status', $veiculo->status) == $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-lg me-1"></i> Atualizar Veículo
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection