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
                            @php $carretaLigada = $veiculo->carretas->firstWhere('status', 'ativo'); @endphp
                            <option value="{{ $veiculo->id }}"
                                {{ old('veiculo_id', request('veiculo_id')) == $veiculo->id ? 'selected' : '' }}>
                                {{ $veiculo->placa }} — {{ $veiculo->modelo }}
                                @if($carretaLigada)
                                    (+ Carreta {{ $carretaLigada->placa }})
                                @elseif($veiculo->carretas->isNotEmpty())
                                    (carreta vinculada indisponível)
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('veiculo_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Carreta</label>
                    <select name="carreta_id" class="form-select @error('carreta_id') is-invalid @enderror">
                        <option value="">— Nenhuma / não se aplica —</option>
                        @foreach($carretas as $carreta)
                            <option value="{{ $carreta->id }}"
                                {{ old('carreta_id', request('carreta_id')) == $carreta->id ? 'selected' : '' }}>
                                {{ $carreta->placa }} — {{ $carreta->modelo }}
                            </option>
                        @endforeach
                    </select>
                    @error('carreta_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted">Só faz sentido se o veículo escolhido for um cavalo mecânico — o rótulo dele já mostra a carreta de sempre; troque aqui se ela estiver indisponível</small>
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
                    <small id="total-com-destinos" class="d-block fw-semibold text-primary mt-1 d-none"></small>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Destinos adicionais</label>
                    <div class="text-muted small mb-2">
                        Se essa viagem entrega em mais de uma cidade (ex.: coleta em São Paulo →
                        Salvador → Maceió), adicione os destinos extras aqui — cada um vira
                        sugestão de carga ao confirmar a viagem.
                    </div>
                    <div id="destinos-adicionais"></div>
                    <button type="button" id="adicionar-destino" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-plus-lg me-1"></i> Adicionar destino
                    </button>
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
    function ligarSelectCidadePorElemento(ufSelect, cidadeSelect, codigoInput, valorAntigo, codigoAntigo) {
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

    function ligarSelectCidade(ufId, cidadeId, codigoId, valorAntigo, codigoAntigo) {
        ligarSelectCidadePorElemento(
            document.getElementById(ufId), document.getElementById(cidadeId), document.getElementById(codigoId),
            valorAntigo, codigoAntigo
        );
    }

    ligarSelectCidade('origem_uf', 'origem_cidade', 'origem_codigo_municipio', @json(old('origem')), @json(old('origem_codigo_municipio')));
    ligarSelectCidade('destino_uf', 'destino_cidade', 'destino_codigo_municipio', @json(old('destino')), @json(old('destino_codigo_municipio')));

    // ── Destinos adicionais (coleta única, várias entregas) ──
    const ufsDisponiveis = @json($ufs);
    let destinoIndex = 0;
    const destinosContainer = document.getElementById('destinos-adicionais');

    function criarLinhaDestino(dados = {}) {
        const indice = destinoIndex++;
        const div = document.createElement('div');
        div.className = 'row g-2 align-items-start mb-2 destino-adicional-row';
        div.innerHTML = `
            <div class="col-md-2">
                <select name="destinos[${indice}][uf]" class="form-select form-select-sm destino-uf">
                    <option value="">UF</option>
                    ${ufsDisponiveis.map(uf => `<option value="${uf}">${uf}</option>`).join('')}
                </select>
            </div>
            <div class="col-md-4">
                <select name="destinos[${indice}][cidade]" class="form-select form-select-sm destino-cidade">
                    <option value="">Selecione a UF primeiro</option>
                </select>
                <input type="hidden" name="destinos[${indice}][codigo_municipio]" class="destino-codigo">
            </div>
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text">R$</span>
                    <input type="number" name="destinos[${indice}][valor_frete]" class="form-control destino-valor"
                           step="0.01" min="0" placeholder="Valor desta etapa">
                </div>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-sm btn-outline-danger w-100 remover-destino">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
        destinosContainer.appendChild(div);

        const ufSelect = div.querySelector('.destino-uf');
        const cidadeSelect = div.querySelector('.destino-cidade');
        const codigoInput = div.querySelector('.destino-codigo');
        const valorInput = div.querySelector('.destino-valor');

        if (dados.uf) ufSelect.value = dados.uf;
        ligarSelectCidadePorElemento(ufSelect, cidadeSelect, codigoInput, dados.cidade || null, dados.codigo_municipio || null);
        if (dados.valor_frete) valorInput.value = dados.valor_frete;

        valorInput.addEventListener('input', atualizarTotalComDestinos);
        div.querySelector('.remover-destino').addEventListener('click', function () {
            div.remove();
            atualizarTotalComDestinos();
        });

        atualizarTotalComDestinos();
    }

    function atualizarTotalComDestinos() {
        const totalEl = document.getElementById('total-com-destinos');
        const valorPrincipal = parseFloat(document.getElementById('valor_frete').value) || 0;
        const valoresAdicionais = Array.from(document.querySelectorAll('.destino-valor'))
            .reduce((soma, input) => soma + (parseFloat(input.value) || 0), 0);

        if (valoresAdicionais > 0) {
            const total = valorPrincipal + valoresAdicionais;
            totalEl.textContent = 'Total da programação (todas as etapas): R$ ' +
                total.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
            totalEl.classList.remove('d-none');
        } else {
            totalEl.classList.add('d-none');
        }
    }

    document.getElementById('adicionar-destino').addEventListener('click', () => criarLinhaDestino());
    document.getElementById('valor_frete').addEventListener('input', atualizarTotalComDestinos);

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
