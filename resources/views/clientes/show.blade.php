@extends('layouts.app')
@section('title', 'Cliente')

@php
    $ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $cliente->nome }}</h4>
        <small class="text-muted">{{ $cliente->documento_formatado }}</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-1"></i> Editar
        </a>
        <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Voltar
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card mb-4 border-start border-primary border-3">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-person-vcard me-2 text-primary"></i>Dados do Cliente
            </div>
            <div class="card-body">
                <div class="table-responsive">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">Tipo</td>
                        <td>{{ $cliente->tipo_pessoa === 'juridica' ? 'Pessoa Jurídica' : 'Pessoa Física' }}</td></tr>
                    <tr><td class="text-muted">{{ $cliente->tipo_pessoa === 'juridica' ? 'CNPJ' : 'CPF' }}</td>
                        <td class="fw-semibold">
                            @if($cliente->tipo_pessoa === 'fisica')
                                <x-dado-sensivel :mascarado="$cliente->documento_mascarado" :completo="$cliente->documento_formatado" />
                            @else
                                {{ $cliente->documento_formatado }}
                            @endif
                        </td></tr>
                    @if($cliente->razao_social)
                    <tr><td class="text-muted">Razão Social</td><td>{{ $cliente->razao_social }}</td></tr>
                    @endif
                    @if($cliente->ie)
                    <tr><td class="text-muted">IE</td><td>{{ $cliente->ie }}</td></tr>
                    @endif
                    <tr><td class="text-muted">Telefone</td><td>{{ $cliente->telefone ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Celular</td><td>{{ $cliente->celular ?? '-' }}</td></tr>
                    <tr><td class="text-muted">E-mail</td><td>{{ $cliente->email ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Contato</td><td>{{ $cliente->contato ?? '-' }}</td></tr>
                    @if($cliente->tabela_frete)
                    <tr><td class="text-muted">Frete Padrão</td>
                        <td>R$ {{ number_format($cliente->tabela_frete, 2, ',', '.') }}/km</td></tr>
                    @endif
                    <tr><td class="text-muted">Status</td>
                        <td><span class="badge {{ $cliente->status === 'ativo' ? 'bg-success' : 'bg-secondary' }}">
                            {{ ucfirst($cliente->status) }}</span></td></tr>
                </table>
                </div>
                <div class="text-muted border-top pt-2 mt-2" style="font-size:.75rem">
                    <i class="bi bi-person-plus me-1"></i>Cadastrado por {{ $cliente->criadoPor?->name ?? 'desconhecido' }}
                </div>
            </div>
        </div>

        @if($cliente->logradouro)
        <div class="card border-start border-info border-3">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-geo-alt me-2 text-primary"></i>Endereço
            </div>
            <div class="card-body">
                <p class="mb-1">{{ $cliente->logradouro }}, {{ $cliente->numero }}</p>
                @if($cliente->complemento)
                    <p class="mb-1">{{ $cliente->complemento }}</p>
                @endif
                <p class="mb-1">{{ $cliente->bairro }}</p>
                <p class="mb-1">{{ $cliente->cidade }}/{{ $cliente->estado }}</p>
                <p class="mb-0 text-muted">CEP: {{ $cliente->cep }}</p>
            </div>
        </div>
        @endif

        <div class="card border-start border-success border-3 mt-4">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-signpost-2 me-2 text-primary"></i>Tabela de Frete por Rota</span>
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#novaRotaFreteModal">
                    <i class="bi bi-plus-lg"></i> Rota
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Rota</th>
                            <th>Valor</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cliente->tabelasFrete as $rota)
                        <tr>
                            <td class="ps-3 small">{{ $rota->origem }}/{{ $rota->origem_uf }} → {{ $rota->destino }}/{{ $rota->destino_uf }}</td>
                            <td>R$ {{ number_format($rota->valor_frete, 2, ',', '.') }}</td>
                            <td class="text-end pe-2">
                                <form action="{{ route('tabela-frete.destroy', $rota) }}" method="POST"
                                      onsubmit="return confirm('Remover esta rota da tabela de frete?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Remover">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3 small">
                                Nenhuma rota cadastrada — o frete deste cliente é preenchido manualmente em cada viagem.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card border-start border-secondary border-3">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-truck me-2 text-primary"></i>Histórico de Viagens
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Motorista</th>
                            <th>Rota</th>
                            <th>Saída</th>
                            <th>Frete</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($viagens as $viagem)
                        <tr onclick="window.location='{{ route('viagens.show', $viagem) }}'"
                            style="cursor:pointer">
                            <td class="ps-3 text-muted">#{{ $viagem->id }}</td>
                            <td>{{ $viagem->motorista->nome }}</td>
                            <td class="small">{{ $viagem->origem }} → {{ $viagem->destino }}</td>
                            <td>{{ $viagem->data_saida->format('d/m/Y') }}</td>
                            <td>R$ {{ number_format($viagem->valor_frete, 2, ',', '.') }}</td>
                            <td><span class="badge badge-status-{{ $viagem->status }}">
                                {{ ucfirst(str_replace('_', ' ', $viagem->status)) }}</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Nenhuma viagem vinculada a este cliente.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
            @if($viagens->hasPages())
            <div class="card-footer">{{ $viagens->links() }}</div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="novaRotaFreteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('clientes.tabela-frete.store', $cliente) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title mb-0">Nova rota — {{ $cliente->nome }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-4">
                            <label class="form-label fw-semibold">UF Origem *</label>
                            <select name="origem_uf" id="rotafrete_origem_uf" class="form-select" required>
                                <option value="">UF</option>
                                @foreach($ufs as $uf)
                                    <option value="{{ $uf }}">{{ $uf }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-8">
                            <label class="form-label fw-semibold">Cidade Origem *</label>
                            <select name="origem" id="rotafrete_origem_cidade" class="form-select" required>
                                <option value="">Selecione a UF primeiro</option>
                            </select>
                            <input type="hidden" name="origem_codigo_municipio" id="rotafrete_origem_codigo">
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-semibold">UF Destino *</label>
                            <select name="destino_uf" id="rotafrete_destino_uf" class="form-select" required>
                                <option value="">UF</option>
                                @foreach($ufs as $uf)
                                    <option value="{{ $uf }}">{{ $uf }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-8">
                            <label class="form-label fw-semibold">Cidade Destino *</label>
                            <select name="destino" id="rotafrete_destino_cidade" class="form-select" required>
                                <option value="">Selecione a UF primeiro</option>
                            </select>
                            <input type="hidden" name="destino_codigo_municipio" id="rotafrete_destino_codigo">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Valor do Frete *</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" name="valor_frete" class="form-control"
                                       step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm">Adicionar Rota</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // ── Cidade por UF (API pública do IBGE) — mesmo padrão usado em viagens/create
    function ligarSelectCidadeFrete(ufId, cidadeId, codigoId) {
        const ufSelect = document.getElementById(ufId);
        const cidadeSelect = document.getElementById(cidadeId);
        const codigoInput = document.getElementById(codigoId);

        ufSelect.addEventListener('change', function () {
            if (! this.value) {
                cidadeSelect.innerHTML = '<option value="">Selecione a UF primeiro</option>';
                codigoInput.value = '';
                return;
            }
            cidadeSelect.innerHTML = '<option value="">Carregando...</option>';
            fetch(`https://servicodados.ibge.gov.br/api/v1/localidades/estados/${this.value}/municipios`)
                .then(r => r.json())
                .then(municipios => {
                    cidadeSelect.innerHTML = '<option value="">Selecione a cidade</option>' +
                        municipios.map(m => `<option value="${m.nome}" data-codigo="${m.id}">${m.nome}</option>`).join('');
                })
                .catch(() => { cidadeSelect.innerHTML = '<option value="">Erro ao carregar cidades</option>'; });
        });

        cidadeSelect.addEventListener('change', function () {
            codigoInput.value = this.selectedOptions[0]?.dataset.codigo || '';
        });
    }

    ligarSelectCidadeFrete('rotafrete_origem_uf', 'rotafrete_origem_cidade', 'rotafrete_origem_codigo');
    ligarSelectCidadeFrete('rotafrete_destino_uf', 'rotafrete_destino_cidade', 'rotafrete_destino_codigo');
</script>
@endpush
@endsection