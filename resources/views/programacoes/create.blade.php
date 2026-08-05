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

            @php
                $ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
            @endphp
            <div class="row g-3 mb-4">
                <div class="col-md-2">
                    <label class="form-label fw-semibold">UF Origem *</label>
                    <select name="origem_uf" id="origem_uf" class="form-select" required>
                        <option value="">UF</option>
                        @foreach($ufs as $uf)
                            <option value="{{ $uf }}" {{ old('origem_uf') === $uf ? 'selected' : '' }}>{{ $uf }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Cidade Origem *</label>
                    <select name="origem" id="origem_cidade" class="form-select @error('origem') is-invalid @enderror" required>
                        <option value="">Selecione a UF primeiro</option>
                    </select>
                    <input type="hidden" name="origem_codigo_municipio" id="origem_codigo_municipio" value="{{ old('origem_codigo_municipio') }}">
                    @error('origem')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">UF Destino *</label>
                    <select name="destino_uf" id="destino_uf" class="form-select" required>
                        <option value="">UF</option>
                        @foreach($ufs as $uf)
                            <option value="{{ $uf }}" {{ old('destino_uf') === $uf ? 'selected' : '' }}>{{ $uf }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Cidade Destino *</label>
                    <select name="destino" id="destino_cidade" class="form-select @error('destino') is-invalid @enderror" required>
                        <option value="">Selecione a UF primeiro</option>
                    </select>
                    <input type="hidden" name="destino_codigo_municipio" id="destino_codigo_municipio" value="{{ old('destino_codigo_municipio') }}">
                    @error('destino')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Cliente</label>
                    <select name="cliente_id" id="cliente_id" class="form-select">
                        <option value="">Selecione o cliente</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}"
                                {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Data Prevista *</label>
                    <input type="date" name="data_prevista"
                           class="form-control @error('data_prevista') is-invalid @enderror"
                           value="{{ old('data_prevista', date('Y-m-d')) }}" required>
                    @error('data_prevista')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Hora da Coleta</label>
                    <input type="time" name="hora_coleta"
                           class="form-control @error('hora_coleta') is-invalid @enderror"
                           value="{{ old('hora_coleta') }}">
                    @error('hora_coleta')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted">Usada pro alerta de risco de no-show</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Valor do Frete</label>
                    <div class="input-group">
                        <span class="input-group-text">R$</span>
                        <input type="number" name="valor_frete" id="valor_frete"
                               class="form-control @error('valor_frete') is-invalid @enderror"
                               value="{{ old('valor_frete') }}" step="0.01" min="0">
                        @error('valor_frete')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <small class="text-muted">Opcional — preencha se já estiver negociado, ou selecione cliente + rota para receber uma sugestão automática</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Data de Entrega Prevista</label>
                    <input type="date" name="data_entrega_prevista"
                           class="form-control @error('data_entrega_prevista') is-invalid @enderror"
                           value="{{ old('data_entrega_prevista') }}">
                    @error('data_entrega_prevista')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Hora de Entrega Prevista</label>
                    <input type="time" name="hora_entrega_prevista"
                           class="form-control @error('hora_entrega_prevista') is-invalid @enderror"
                           value="{{ old('hora_entrega_prevista') }}">
                    @error('hora_entrega_prevista')<div class="invalid-feedback">{{ $message }}</div>@enderror
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

@push('scripts')
<script>
    // ── Cidade por UF (API pública do IBGE) — mesmo padrão usado em viagens/create
    function ligarSelectCidade(ufId, cidadeId, codigoId, valorAntigo, codigoAntigo) {
        const ufSelect = document.getElementById(ufId);
        const cidadeSelect = document.getElementById(cidadeId);
        const codigoInput = document.getElementById(codigoId);

        function carregarCidades(ufSelecionada, selecionarCidade) {
            if (! ufSelecionada) {
                cidadeSelect.innerHTML = '<option value="">Selecione a UF primeiro</option>';
                return;
            }
            cidadeSelect.innerHTML = '<option value="">Carregando...</option>';
            fetch(`https://servicodados.ibge.gov.br/api/v1/localidades/estados/${ufSelecionada}/municipios`)
                .then(r => r.json())
                .then(municipios => {
                    cidadeSelect.innerHTML = '<option value="">Selecione a cidade</option>' +
                        municipios.map(m => `<option value="${m.nome}" data-codigo="${m.id}">${m.nome}</option>`).join('');
                    if (selecionarCidade) {
                        cidadeSelect.value = selecionarCidade;
                        codigoInput.value = cidadeSelect.selectedOptions[0]?.dataset.codigo || codigoAntigo || '';
                    }
                })
                .catch(() => { cidadeSelect.innerHTML = '<option value="">Erro ao carregar cidades</option>'; });
        }

        ufSelect.addEventListener('change', function () { carregarCidades(this.value, null); });
        cidadeSelect.addEventListener('change', function () {
            codigoInput.value = this.selectedOptions[0]?.dataset.codigo || '';
        });

        if (ufSelect.value) {
            carregarCidades(ufSelect.value, valorAntigo);
        }
    }

    ligarSelectCidade('origem_uf', 'origem_cidade', 'origem_codigo_municipio', @json(old('origem')), @json(old('origem_codigo_municipio')));
    ligarSelectCidade('destino_uf', 'destino_cidade', 'destino_codigo_municipio', @json(old('destino')), @json(old('destino_codigo_municipio')));

    // ── Sugestão automática de valor_frete a partir da tabela de frete do cliente
    let valorFreteEditadoManualmente = false;
    let preenchendoValorFreteAuto = false;

    document.getElementById('valor_frete').addEventListener('input', function () {
        if (! preenchendoValorFreteAuto) valorFreteEditadoManualmente = true;
    });

    function buscarSugestaoFrete() {
        if (valorFreteEditadoManualmente) return;

        const clienteId     = document.getElementById('cliente_id').value;
        const origemCodigo  = document.getElementById('origem_codigo_municipio').value;
        const destinoCodigo = document.getElementById('destino_codigo_municipio').value;

        if (! clienteId || ! origemCodigo || ! destinoCodigo) return;

        const params = new URLSearchParams({
            cliente_id: clienteId,
            origem_codigo_municipio: origemCodigo,
            destino_codigo_municipio: destinoCodigo,
        });

        fetch(`{{ route('tabela-frete.sugestao') }}?${params}`)
            .then(r => r.json())
            .then(({ valor }) => {
                if (valor === null || valorFreteEditadoManualmente) return;
                preenchendoValorFreteAuto = true;
                document.getElementById('valor_frete').value = parseFloat(valor).toFixed(2);
                preenchendoValorFreteAuto = false;
            })
            .catch(() => {});
    }

    document.getElementById('cliente_id').addEventListener('change', buscarSugestaoFrete);
    document.getElementById('origem_cidade').addEventListener('change', buscarSugestaoFrete);
    document.getElementById('destino_cidade').addEventListener('change', buscarSugestaoFrete);
</script>
@endpush
@endsection
