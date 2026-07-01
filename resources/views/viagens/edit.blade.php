@extends('layouts.app')
@section('title', 'Editar Viagem')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Editar Viagem #{{ $viagem->id }}</h4>
        <small class="text-muted">{{ $viagem->motorista->nome }} — {{ $viagem->origem }} → {{ $viagem->destino }}</small>
    </div>
    <a href="{{ route('viagens.show', $viagem) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body p-4">
        <form action="{{ route('viagens.update', $viagem) }}" method="POST">
            @csrf @method('PUT')

            <h6 class="fw-bold text-uppercase text-muted mb-3" style="font-size:.75rem;letter-spacing:1px">
                <i class="bi bi-person-badge me-1"></i> Motorista e Veículo
            </h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Motorista *</label>
                    <select name="motorista_id" class="form-select" required>
                        @foreach($motoristas as $motorista)
                            <option value="{{ $motorista->id }}"
                                {{ old('motorista_id', $viagem->motorista_id) == $motorista->id ? 'selected' : '' }}>
                                {{ $motorista->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Veículo *</label>
                    <select name="veiculo_id" class="form-select" required>
                        @foreach($veiculos as $veiculo)
                            <option value="{{ $veiculo->id }}"
                                {{ old('veiculo_id', $viagem->veiculo_id) == $veiculo->id ? 'selected' : '' }}>
                                {{ $veiculo->placa }} — {{ $veiculo->modelo }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <h6 class="fw-bold text-uppercase text-muted mb-3" style="font-size:.75rem;letter-spacing:1px">
                <i class="bi bi-map me-1"></i> Rota e Datas
            </h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Origem *</label>
                    <input type="text" name="origem" class="form-control"
                           value="{{ old('origem', $viagem->origem) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Destino *</label>
                    <input type="text" name="destino" class="form-control"
                           value="{{ old('destino', $viagem->destino) }}" required>
                </div>
                <<div class="col-md-4">
                    <label class="form-label fw-semibold">Cliente</label>
                    <select name="cliente_id" class="form-select">
                        <option value="">Selecione o cliente</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}"
                                {{ old('cliente_id', $viagem->cliente_id) == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Data Saída *</label>
                    <input type="date" name="data_saida" class="form-control"
                           value="{{ old('data_saida', $viagem->data_saida->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Data Retorno</label>
                    <input type="date" name="data_retorno" class="form-control"
                           value="{{ old('data_retorno', $viagem->data_retorno?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">KM Inicial</label>
                    <input type="number" name="km_inicial" class="form-control"
                           value="{{ old('km_inicial', $viagem->km_inicial) }}" min="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">KM Final</label>
                    <input type="number" name="km_final" class="form-control"
                           value="{{ old('km_final', $viagem->km_final) }}" min="0">
                </div>
            </div>

            <h6 class="fw-bold text-uppercase text-muted mb-3" style="font-size:.75rem;letter-spacing:1px">
                <i class="bi bi-cash-stack me-1"></i> Financeiro e Status
            </h6>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Valor do Frete *</label>
                    <div class="input-group">
                        <span class="input-group-text">R$</span>
                        <input type="number" name="valor_frete" class="form-control"
                               value="{{ old('valor_frete', $viagem->valor_frete) }}"
                               step="0.01" min="0" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">% Comissão *</label>
                    <div class="input-group">
                        <input type="number" name="percentual_motorista" class="form-control"
                               value="{{ old('percentual_motorista', $viagem->percentual_motorista) }}"
                               step="0.01" min="0" max="100" required>
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Adiantamento (Vale-Viagem)</label>
                    <div class="input-group">
                        <span class="input-group-text">R$</span>
                        <input type="number" name="valor_adiantamento" class="form-control"
                            value="{{ old('valor_adiantamento', $viagem->valor_adiantamento) }}"
                            step="0.01" min="0">
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox"
                            name="adiantamento_descontavel"
                            id="adiantamento_descontavel"
                            value="1"
                            {{ old('adiantamento_descontavel', $viagem->adiantamento_descontavel) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="adiantamento_descontavel">
                            Descontar do motorista?
                        </label>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Status *</label>
                    <select name="status" class="form-select" required>
                        @foreach([
                            'aberta'            => 'Aberta',
                            'em_andamento'      => 'Em Andamento',
                            'aguardando_acerto' => 'Aguardando Acerto',
                            'encerrada'         => 'Encerrada'
                        ] as $val => $label)
                            <option value="{{ $val }}"
                                {{ old('status', $viagem->status) == $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Observações</label>
                <textarea name="observacoes" class="form-control" rows="3">{{ old('observacoes', $viagem->observacoes) }}</textarea>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check-lg me-1"></i> Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>
@endsection