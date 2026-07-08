@extends('layouts.app')
@section('title', 'Nova Programação')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Programar Próxima Viagem</h4>
        <small class="text-muted">Defina motorista, veículo e cliente com antecedência</small>
    </div>
    <a href="{{ route('programacoes.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body p-4">
        <form action="{{ route('programacoes.store') }}" method="POST">
            @csrf
            <input type="hidden" name="viagem_origem_id" value="{{ old('viagem_origem_id', request('viagem_origem_id')) }}">

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Motorista *</label>
                    <select name="motorista_id" class="form-select @error('motorista_id') is-invalid @enderror" required>
                        <option value="">Selecione o motorista</option>
                        @foreach($motoristas as $motorista)
                            <option value="{{ $motorista->id }}"
                                {{ old('motorista_id', request('motorista_id')) == $motorista->id ? 'selected' : '' }}>
                                {{ $motorista->nome }}
                            </option>
                        @endforeach
                    </select>
                    @error('motorista_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Veículo *</label>
                    <select name="veiculo_id" class="form-select @error('veiculo_id') is-invalid @enderror" required>
                        <option value="">Selecione o veículo</option>
                        @foreach($veiculos as $veiculo)
                            <option value="{{ $veiculo->id }}"
                                {{ old('veiculo_id', request('veiculo_id')) == $veiculo->id ? 'selected' : '' }}>
                                {{ $veiculo->placa }} — {{ $veiculo->modelo }}
                            </option>
                        @endforeach
                    </select>
                    @error('veiculo_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Origem *</label>
                    <input type="text" name="origem" class="form-control @error('origem') is-invalid @enderror"
                           value="{{ old('origem') }}" required>
                    @error('origem')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Destino *</label>
                    <input type="text" name="destino" class="form-control @error('destino') is-invalid @enderror"
                           value="{{ old('destino') }}" required>
                    @error('destino')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Cliente</label>
                    <select name="cliente_id" class="form-select">
                        <option value="">Selecione o cliente</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}"
                                {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Data Prevista *</label>
                    <input type="date" name="data_prevista"
                           class="form-control @error('data_prevista') is-invalid @enderror"
                           value="{{ old('data_prevista', date('Y-m-d')) }}" required>
                    @error('data_prevista')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Observações</label>
                <textarea name="observacoes" class="form-control" rows="3">{{ old('observacoes') }}</textarea>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-signpost-2 me-1"></i> Programar Viagem
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
