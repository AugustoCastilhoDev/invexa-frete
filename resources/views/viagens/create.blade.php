@extends('layouts.app')
@section('title', 'Nova Viagem')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Nova Viagem</h4>
        <small class="text-muted">Preencha os dados para abrir a viagem</small>
    </div>
    <a href="{{ route('viagens.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body p-4">
        <form action="{{ route('viagens.store') }}" method="POST">
            @csrf
            <input type="hidden" name="programacao_id" value="{{ request('programacao_id') }}">

            @if($programacao)
            <div class="alert alert-info py-2 small mb-4">
                <i class="bi bi-signpost-2 me-1"></i>
                Abrindo a partir da programação #{{ $programacao->id }} — ao salvar, ela será marcada como confirmada.
            </div>
            @endif

            <h6 class="fw-bold text-uppercase text-muted mb-3" style="font-size:.75rem;letter-spacing:1px">
                <i class="bi bi-person-badge me-1"></i> Motorista e Veículo
            </h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Motorista *</label>
                    <select name="motorista_id" id="motorista_id"
                            class="form-select @error('motorista_id') is-invalid @enderror" required>
                        <option value="">Selecione o motorista</option>
                        @foreach($motoristas as $motorista)
                            <option value="{{ $motorista->id }}"
                                    data-comissao="{{ $motorista->percentual_comissao }}"
                                    {{ old('motorista_id', request('motorista_id')) == $motorista->id ? 'selected' : '' }}>
                                {{ $motorista->nome }}
                            </option>
                        @endforeach
                    </select>
                    @error('motorista_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Veículo *</label>
                    <select name="veiculo_id"
                            class="form-select @error('veiculo_id') is-invalid @enderror" required>
                        <option value="">Selecione o veículo</option>
                        @foreach($veiculos as $veiculo)
                            <option value="{{ $veiculo->id }}"
                                {{ old('veiculo_id', request('veiculo_id')) == $veiculo->id ? 'selected' : '' }}
                                {{ $veiculosBloqueados->contains($veiculo->id) ? 'disabled' : '' }}>
                                {{ $veiculo->placa }} — {{ $veiculo->modelo }}
                                {{ $veiculosBloqueados->contains($veiculo->id) ? '— MDF-e pendente de encerramento' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('veiculo_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <h6 class="fw-bold text-uppercase text-muted mb-3" style="font-size:.75rem;letter-spacing:1px">
                <i class="bi bi-map me-1"></i> Rota e Datas
            </h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Origem *</label>
                    <input type="text" name="origem" class="form-control"
                           value="{{ old('origem', request('origem')) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Destino *</label>
                    <input type="text" name="destino" class="form-control"
                           value="{{ old('destino', request('destino')) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Cliente</label>
                    <select name="cliente_id" class="form-select">
                        <option value="">Selecione o cliente</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}"
                                {{ old('cliente_id', request('cliente_id')) == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Data de Saída *</label>
                    <input type="date" name="data_saida" class="form-control"
                           value="{{ old('data_saida', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">KM Inicial</label>
                    <input type="number" name="km_inicial" class="form-control"
                           value="{{ old('km_inicial') }}" min="0">
                </div>
            </div>

            <h6 class="fw-bold text-uppercase text-muted mb-3" style="font-size:.75rem;letter-spacing:1px">
                <i class="bi bi-cash-stack me-1"></i> Financeiro
            </h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Valor do Frete *</label>
                    <div class="input-group">
                        <span class="input-group-text">R$</span>
                        <input type="number" name="valor_frete" id="valor_frete"
                               class="form-control @error('valor_frete') is-invalid @enderror"
                               value="{{ old('valor_frete', request('valor_frete', '0.00')) }}"
                               step="0.01" min="0" required>
                    </div>
                    @error('valor_frete')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">% Comissão Motorista *</label>
                    <div class="input-group">
                        <input type="number" name="percentual_motorista" id="percentual_motorista"
                               class="form-control" step="0.01" min="0" max="100"
                               value="{{ old('percentual_motorista', '0.00') }}" required>
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Valor do Motorista</label>
                    <div class="input-group">
                        <span class="input-group-text">R$</span>
                        <input type="text" id="valor_motorista_preview"
                               class="form-control bg-light" readonly value="0,00">
                    </div>
                    <small class="text-muted">Calculado automaticamente</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Adiantamento (Vale-Viagem)</label>
                    <div class="input-group">
                        <span class="input-group-text">R$</span>
                        <input type="number" name="valor_adiantamento"
                            class="form-control"
                            value="{{ old('valor_adiantamento', '0.00') }}"
                            step="0.01" min="0">
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox"
                            name="adiantamento_descontavel"
                            id="adiantamento_descontavel"
                            value="1"
                            {{ old('adiantamento_descontavel', true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="adiantamento_descontavel">
                            Descontar do motorista?
                        </label>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Observações</label>
                <textarea name="observacoes" class="form-control" rows="3">{{ old('observacoes') }}</textarea>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-truck me-1"></i> Abrir Viagem
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Preenche % comissão automaticamente ao selecionar motorista
    document.getElementById('motorista_id').addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        const comissao = selected.dataset.comissao || 0;
        document.getElementById('percentual_motorista').value = comissao;
        calcularMotorista();
    });

    function calcularMotorista() {
        const frete     = parseFloat(document.getElementById('valor_frete').value) || 0;
        const percentual = parseFloat(document.getElementById('percentual_motorista').value) || 0;
        const valor     = (frete * percentual / 100).toFixed(2);
        document.getElementById('valor_motorista_preview').value =
            parseFloat(valor).toLocaleString('pt-BR', {minimumFractionDigits: 2});
    }

    document.getElementById('valor_frete').addEventListener('input', calcularMotorista);
    document.getElementById('percentual_motorista').addEventListener('input', calcularMotorista);

    // Ao vir de uma programação já com motorista/frete preenchidos, calcula a prévia de cara
    calcularMotorista();
</script>
@endpush
@endsection