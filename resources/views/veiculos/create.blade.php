@extends('layouts.app')
@section('title', 'Novo Veículo')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Novo Veículo</h4>
        <small class="text-muted">Preencha os dados do veículo</small>
    </div>
    <a href="{{ route('veiculos.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body p-4">
        <form action="{{ route('veiculos.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Placa *</label>
                    <input type="text" name="placa"
                           class="form-control @error('placa') is-invalid @enderror"
                           value="{{ old('placa') }}"
                           placeholder="ABC-1234" required>
                    @error('placa')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Modelo *</label>
                    <input type="text" name="modelo"
                           class="form-control @error('modelo') is-invalid @enderror"
                           value="{{ old('modelo') }}" required>
                    @error('modelo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Marca</label>
                    <input type="text" name="marca" class="form-control"
                           value="{{ old('marca') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Ano</label>
                    <input type="number" name="ano" class="form-control"
                           value="{{ old('ano') }}"
                           min="1990" max="{{ date('Y') + 1 }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Tipo *</label>
                    <select name="tipo" class="form-select" required>
                        @foreach(['truck'=>'Truck','carreta'=>'Carreta','van'=>'Van','utilitario'=>'Utilitário','outro'=>'Outro'] as $val => $label)
                            <option value="{{ $val }}" {{ old('tipo') == $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">RENAVAM</label>
                    <input type="text" name="renavam" class="form-control"
                           value="{{ old('renavam') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Capacidade (kg)</label>
                    <div class="input-group">
                        <input type="number" name="capacidade_kg" class="form-control"
                               value="{{ old('capacidade_kg') }}" step="0.01" min="0">
                        <span class="input-group-text">kg</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="ativo">Ativo</option>
                        <option value="inativo">Inativo</option>
                        <option value="manutencao">Em Manutenção</option>
                    </select>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-lg me-1"></i> Salvar Veículo
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection