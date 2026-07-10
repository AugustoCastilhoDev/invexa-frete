@extends('layouts.app')
@section('title', 'Viagem #' . $viagem->id)

@section('content')

{{-- Cabeçalho --}}
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h4 class="mb-0">Viagem #{{ $viagem->id }}
            <span class="badge badge-status-{{ $viagem->status }} ms-2" style="font-size:.7rem">
                {{ ucfirst(str_replace('_',' ',$viagem->status)) }}
            </span>
        </h4>
        <small class="text-muted">
            {{ $viagem->motorista->nome }} —
            {{ $viagem->veiculo->placa }} —
            {{ $viagem->origem }} → {{ $viagem->destino }}
        </small>
        <div class="text-muted mt-1" style="font-size:.75rem">
            <i class="bi bi-person-plus me-1"></i>Aberta por {{ $viagem->criadoPor?->name ?? 'desconhecido' }}
            @if($viagem->atualizadoPor && $viagem->atualizadoPor->isNot($viagem->criadoPor))
                · <i class="bi bi-pencil-square me-1"></i>última atualização por {{ $viagem->atualizadoPor->name }}
            @endif
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap justify-content-end">
        @if($viagem->status !== 'encerrada')
            <a href="{{ route('viagens.edit', $viagem) }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-pencil me-1"></i> Editar
            </a>

            @if($viagem->proximo_status)
            <form action="{{ route('viagens.avancar-status', $viagem) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('{{ $viagem->proximo_status_label }}?')">
                @csrf @method('PATCH')
                <button class="btn btn-primary btn-sm">
                    <i class="bi bi-arrow-right-circle me-1"></i> {{ $viagem->proximo_status_label }}
                </button>
            </form>
            @elseif($viagem->status === 'aguardando_acerto')
            <form action="{{ route('viagens.encerrar', $viagem) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Encerrar esta viagem?')">
                @csrf @method('PATCH')
                <button class="btn btn-success btn-sm">
                    <i class="bi bi-check-circle me-1"></i> Encerrar Viagem
                </button>
            </form>
            @endif

            @if(in_array($viagem->status, ['em_andamento', 'aguardando_acerto']))
                @if($programacaoPendente)
                <a href="{{ route('programacoes.edit', $programacaoPendente) }}" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-signpost-2 me-1"></i> Próxima Viagem Já Programada
                </a>
                @else
                <a href="{{ route('programacoes.create', ['motorista_id' => $viagem->motorista_id, 'veiculo_id' => $viagem->veiculo_id, 'viagem_origem_id' => $viagem->id]) }}"
                   class="btn btn-outline-success btn-sm">
                    <i class="bi bi-signpost-2 me-1"></i> Programar Próxima Viagem
                </a>
                @endif
            @endif
        @endif
        <a href="{{ route('viagens.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Voltar
        </a>
        <a href="{{ route('viagens.imprimir', $viagem) }}" target="_blank"
           class="btn btn-outline-dark btn-sm">
            <i class="bi bi-printer me-1"></i> Imprimir
        </a>
    </div>
</div>

{{-- Cards de Resumo --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-center border-start border-primary border-3">
            <div class="card-body py-3">
                <div class="text-muted small mb-1">Valor do Frete</div>
                <div class="fw-bold fs-5 text-primary">
                    R$ {{ number_format($viagem->valor_frete, 2, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-start border-warning border-3">
            <div class="card-body py-3">
                <div class="text-muted small mb-1">
                    Motorista ({{ number_format($viagem->percentual_motorista,2,',','.') }}%)
                </div>
                <div class="fw-bold fs-5 text-warning">
                    R$ {{ number_format($viagem->valor_motorista, 2, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-start border-3"
             style="border-color:{{ $viagem->saldo_motorista >= 0 ? '#10b981' : '#ef4444' }}!important">
            <div class="card-body py-3">
                <div class="text-muted small mb-1">Saldo Motorista</div>
                <div class="fw-bold fs-5 {{ $viagem->saldo_motorista >= 0 ? 'text-success' : 'text-danger' }}">
                    R$ {{ number_format($viagem->saldo_motorista, 2, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-start border-3"
             style="border-color:{{ $viagem->lucro_transportadora >= 0 ? '#10b981' : '#ef4444' }}!important">
            <div class="card-body py-3">
                <div class="text-muted small mb-1">Lucro Transportadora</div>
                <div class="fw-bold fs-5 {{ $viagem->lucro_transportadora >= 0 ? 'text-success' : 'text-danger' }}">
                    R$ {{ number_format($viagem->lucro_transportadora, 2, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">

    {{-- Coluna Esquerda --}}
    <div class="col-md-6">

        {{-- Dados da Viagem --}}
        <div class="card mb-4 border-start border-primary border-3">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-info-circle me-2 text-primary"></i>Dados da Viagem
            </div>
            <div class="card-body">
                <div class="table-responsive">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Cliente</td>
                        <td>
                            @if($viagem->cliente)
                                <a href="{{ route('clientes.show', $viagem->cliente) }}"
                                   class="text-decoration-none">
                                    {{ $viagem->cliente->nome }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr><td class="text-muted">Saída</td>
                        <td>{{ $viagem->data_saida->format('d/m/Y') }}</td></tr>
                    <tr><td class="text-muted">Retorno</td>
                        <td>{{ $viagem->data_retorno?->format('d/m/Y') ?? 'Em aberto' }}</td></tr>
                    <tr><td class="text-muted">KM Inicial</td>
                        <td>{{ $viagem->km_inicial ?? '-' }}</td></tr>
                    <tr><td class="text-muted">KM Final</td>
                        <td>{{ $viagem->km_final ?? '-' }}</td></tr>
                    <tr><td class="text-muted">KM Rodados</td>
                        <td>{{ $viagem->km_rodados > 0 ? number_format($viagem->km_rodados, 0, ',', '.').' km' : '-' }}</td></tr>
                    @if($viagem->media_combustivel !== null)
                    <tr><td class="text-muted">Média de Combustível</td>
                        <td>
                            <span class="fw-semibold" style="color:#3b82f6">{{ number_format($viagem->media_combustivel, 2, ',', '.') }} km/L</span>
                            <span class="text-muted small">({{ number_format($viagem->total_litros, 2, ',', '.') }} L abastecidos)</span>
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted">Adiantamento</td>
                        <td>
                            R$ {{ number_format($viagem->valor_adiantamento, 2, ',', '.') }}
                            @if($viagem->valor_adiantamento > 0)
                                <span class="badge {{ $viagem->adiantamento_descontavel ? 'bg-danger' : 'bg-info' }} ms-1">
                                    {{ $viagem->adiantamento_descontavel ? 'Descontado' : 'Não descontado' }}
                                </span>
                            @endif
                        </td>
                    </tr>
                    @if($viagem->observacoes)
                    <tr><td class="text-muted">Obs.</td>
                        <td>{{ $viagem->observacoes }}</td></tr>
                    @endif
                </table>
                </div>
            </div>
        </div>

        {{-- Descontos e Bonificações --}}
        <div class="card mb-4 border-start border-danger border-3">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-dash-circle me-2 text-danger"></i>Descontos e Bonificações</span>
                <span>
                    <span class="text-danger fw-bold">
                        - R$ {{ number_format($viagem->total_descontos, 2, ',', '.') }}
                    </span>
                    @if($viagem->total_bonificacoes > 0)
                    <span class="text-success fw-bold ms-2">
                        + R$ {{ number_format($viagem->total_bonificacoes, 2, ',', '.') }}
                    </span>
                    @endif
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Tipo</th>
                            <th>Descrição</th>
                            <th>Data</th>
                            <th>Valor</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($viagem->descontos as $desconto)
                        @php
                            $ehBonificacao = $desconto->tipo === 'bonificacao';
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <span class="badge bg-{{ $ehBonificacao ? 'success' : 'danger' }} bg-opacity-10 text-{{ $ehBonificacao ? 'success' : 'danger' }}">
                                    {{ ucfirst($desconto->tipo) }}
                                </span>
                            </td>
                            <td>
                                {{ $desconto->descricao }}
                                <br><small class="text-muted">por {{ $desconto->criadoPor?->name ?? '—' }}</small>
                            </td>
                            <td>{{ $desconto->data_desconto->format('d/m/Y') }}</td>
                            <td class="{{ $ehBonificacao ? 'text-success fw-semibold' : '' }}">
                                {{ $ehBonificacao ? '+' : '' }} R$ {{ number_format($desconto->valor, 2, ',', '.') }}
                            </td>
                            <td>
                                @if($viagem->status !== 'encerrada' && auth()->user()?->isAdmin())
                                <form action="{{ route('descontos.destroy', $desconto) }}"
                                      method="POST"
                                      onsubmit="return confirm('Remover lançamento?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-link text-danger p-0">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-2 small">
                            Nenhum desconto ou bonificação lançado.
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
            @if($viagem->status !== 'encerrada')
            <div class="card-footer bg-white">
                <form action="{{ route('descontos.store', $viagem) }}" method="POST">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-4">
                            <select name="tipo" class="form-select form-select-sm" required>
                                <option value="">Tipo</option>
                                <option value="vale">Vale</option>
                                <option value="multa">Multa</option>
                                <option value="adiantamento">Adiantamento</option>
                                <option value="bonificacao">Bonificação (diária/prêmio)</option>
                                <option value="outros">Outros</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="descricao" class="form-control form-control-sm"
                                   placeholder="Descrição" required>
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="valor" class="form-control form-control-sm"
                                   placeholder="Valor" step="0.01" min="0" required>
                        </div>
                        <input type="hidden" name="data_desconto" value="{{ date('Y-m-d') }}">
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-danger btn-sm w-100">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            @endif
        </div>

        {{-- Documentos Fiscais --}}
        <div class="card border-start border-3" style="border-color:#3b82f6!important">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-file-earmark-text me-2 text-primary"></i>Documentos Fiscais</span>
                <div class="d-flex align-items-center gap-2">
                    @if($viagem->empresa->focus_nfe_ativo && $viagem->status !== 'encerrada')
                    <form action="{{ route('viagens.emissoes-fiscais.store', [$viagem, 'cte']) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-primary">Emitir CT-e</button>
                    </form>
                    <form action="{{ route('viagens.emissoes-fiscais.store', [$viagem, 'mdfe']) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-primary">Emitir MDF-e</button>
                    </form>
                    @endif
                    <span class="badge bg-secondary">{{ $viagem->documentos->count() }}</span>
                </div>
            </div>
            @php
                $emissoesPendentes = $viagem->emissoesFiscais->whereNotIn('status', ['autorizado']);
            @endphp
            @if($emissoesPendentes->isNotEmpty())
            <div class="card-body border-bottom py-2">
                @foreach($emissoesPendentes as $emissao)
                <div class="d-flex justify-content-between align-items-center small py-1">
                    <span>
                        <span class="badge bg-warning text-dark">{{ $emissao->tipo_formatado }}</span>
                        {{ $emissao->status }}
                        @if($emissao->mensagem_erro)
                            — <span class="text-danger">{{ $emissao->mensagem_erro }}</span>
                        @endif
                    </span>
                    <form action="{{ route('emissoes-fiscais.atualizar-status', $emissao) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary">Atualizar status</button>
                    </form>
                </div>
                @endforeach
            </div>
            @endif
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Tipo</th>
                            <th>Número</th>
                            <th>Série</th>
                            <th>Emissão</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($viagem->documentos as $doc)
                        <tr>
                            <td class="ps-3">
                                <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold">
                                    {{ $doc->tipo_formatado }}
                                </span>
                            </td>
                            <td class="fw-semibold">
                                {{ $doc->numero }}
                                <br><small class="text-muted fw-normal">por {{ $doc->criadoPor?->name ?? '—' }}</small>
                            </td>
                            <td>{{ $doc->serie ?? '-' }}</td>
                            <td>{{ $doc->data_emissao->format('d/m/Y') }}</td>
                            <td>R$ {{ number_format($doc->valor, 2, ',', '.') }}</td>
                            <td>
                                <span class="badge bg-{{ $doc->status_badge }}">
                                    {{ ucfirst($doc->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    @if($doc->arquivo)
                                    <a href="{{ $doc->arquivo_url }}"
                                       target="_blank"
                                       class="btn btn-sm btn-outline-secondary"
                                       title="Baixar arquivo">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    @endif
                                    @if($doc->url_consulta_sefaz)
                                    <a href="{{ $doc->url_consulta_sefaz }}"
                                       target="_blank"
                                       class="btn btn-sm btn-outline-primary"
                                       title="Verificar na SEFAZ{{ $doc->exige_login_gov_br ? ' (exige login gov.br)' : '' }} — chave: {{ $doc->chave_acesso }}">
                                        <i class="bi bi-patch-check"></i>
                                    </a>
                                    @elseif(in_array($doc->tipo, ['cte', 'mdfe', 'nfe']))
                                    <span class="text-muted d-inline-flex align-items-center"
                                          title="Sem chave de acesso cadastrada — não é possível verificar na SEFAZ. Clique no lápis para adicionar.">
                                        <i class="bi bi-question-circle"></i>
                                    </span>
                                    @endif
                                    @if($viagem->status !== 'encerrada')
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                            data-bs-toggle="modal" data-bs-target="#editarChaveDoc{{ $doc->id }}"
                                            title="Editar chave de acesso">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    @if(auth()->user()?->isAdmin())
                                    <form action="{{ route('documentos.destroy', $doc) }}"
                                          method="POST"
                                          onsubmit="return confirm('Remover documento?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @if($viagem->status !== 'encerrada')
                        <div class="modal fade" id="editarChaveDoc{{ $doc->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('documentos.update', $doc) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $doc->status }}">
                                        <div class="modal-header">
                                            <h6 class="modal-title mb-0">
                                                Editar chave de acesso — {{ $doc->tipo_formatado }} {{ $doc->numero }}
                                            </h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <label class="form-label small fw-semibold">Chave de Acesso (44 dígitos)</label>
                                            <input type="text" name="chave_acesso" class="form-control"
                                                   maxlength="60" value="{{ $doc->chave_acesso }}"
                                                   placeholder="Chave de Acesso (44 dígitos)">
                                            <div class="form-text">Necessária para o botão "Verificar na SEFAZ" aparecer.</div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary btn-sm">Salvar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-2 small">
                                Nenhum documento fiscal lançado.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
            @if($viagem->status !== 'encerrada')
            <div class="card-footer bg-white">
                <form action="{{ route('documentos.store', $viagem) }}"
                      method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-2">
                            <select name="tipo" class="form-select form-select-sm" required>
                                <option value="">Tipo</option>
                                <option value="cte">CT-e</option>
                                <option value="mdfe">MDF-e</option>
                                <option value="nfe">NF-e</option>
                                <option value="outros">Outros</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="numero"
                                   class="form-control form-control-sm"
                                   placeholder="Número" required>
                        </div>
                        <div class="col-md-1">
                            <input type="text" name="serie"
                                   class="form-control form-control-sm"
                                   placeholder="Série">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="data_emissao"
                                   class="form-control form-control-sm"
                                   value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-2">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">R$</span>
                                <input type="number" name="valor"
                                       class="form-control form-control-sm"
                                       placeholder="Valor" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select form-select-sm" required>
                                <option value="pendente">Pendente</option>
                                <option value="autorizado">Autorizado</option>
                                <option value="cancelado">Cancelado</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="chave_acesso"
                                   class="form-control form-control-sm"
                                   placeholder="Chave de Acesso (44 dígitos)" maxlength="60">
                        </div>
                        <div class="col-md-4">
                            <input type="file" name="arquivo"
                                   class="form-control form-control-sm"
                                   accept=".xml,.pdf">
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="observacao"
                                   class="form-control form-control-sm"
                                   placeholder="Observação">
                        </div>
                    </div>
                </form>
            </div>
            @endif
        </div>

    </div>

    {{-- Coluna Direita --}}
    <div class="col-md-6">

        {{-- Lançamentos --}}
        <div class="card mb-4 border-start border-warning border-3">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-receipt me-2 text-warning"></i>Lançamentos</span>
                <span class="text-warning fw-bold">
                    R$ {{ number_format($viagem->total_combustivel + $viagem->total_manutencao, 2, ',', '.') }}
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Tipo</th>
                            <th>Descrição</th>
                            <th>Data</th>
                            <th>KM</th>
                            <th>Litros</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($viagem->lancamentos as $lancamento)
                        <tr>
                            <td class="ps-3">
                                @php
                                    $cor = match($lancamento->tipo) {
                                        'combustivel' => 'warning',
                                        'manutencao'  => 'danger',
                                        default       => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $cor }} bg-opacity-10 text-{{ $cor }}">
                                    {{ ucfirst($lancamento->tipo) }}
                                </span>
                            </td>
                            <td>
                                {{ $lancamento->descricao }}
                                <br><small class="text-muted">por {{ $lancamento->criadoPor?->name ?? '—' }}</small>
                                @if($lancamento->comprovante)
                                <br><a href="{{ $lancamento->comprovante_url }}" target="_blank" class="small">
                                    <i class="bi bi-paperclip"></i> Comprovante
                                </a>
                                @endif
                            </td>
                            <td>{{ $lancamento->data_lancamento->format('d/m/Y') }}</td>
                            <td>{{ $lancamento->km_veiculo ? number_format($lancamento->km_veiculo, 0, ',', '.') : '-' }}</td>
                            <td>{{ $lancamento->litros ? number_format($lancamento->litros, 2, ',', '.') : '-' }}</td>
                            <td>R$ {{ number_format($lancamento->valor, 2, ',', '.') }}</td>
                            <td>
                                <span class="badge bg-{{ $lancamento->status_badge }}">
                                    {{ ucfirst($lancamento->status) }}
                                </span>
                            </td>
                            <td class="text-end pe-2">
                                @if($lancamento->status === 'pendente')
                                <form action="{{ route('lancamentos.aprovar', $lancamento) }}" method="POST" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-link text-success p-0 me-2" title="Aprovar">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                </form>
                                <form action="{{ route('lancamentos.rejeitar', $lancamento) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Rejeitar este lançamento?')">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-link text-danger p-0 me-2" title="Rejeitar">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </form>
                                @endif
                                @if($viagem->status !== 'encerrada' && auth()->user()?->isAdmin())
                                <form action="{{ route('lancamentos.destroy', $lancamento) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('Remover lançamento?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-link text-danger p-0">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center text-muted py-2 small">
                            Nenhum lançamento registrado.
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
            @if($viagem->status !== 'encerrada')
            <div class="card-footer bg-white">
                <form action="{{ route('lancamentos.store', $viagem) }}"
                      method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-2">
                            <select name="tipo" id="lancamento-tipo" class="form-select form-select-sm" required>
                                <option value="">Tipo</option>
                                <option value="combustivel">Combustível</option>
                                <option value="manutencao">Manutenção</option>
                                <option value="outros">Outros</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="descricao" class="form-control form-control-sm"
                                   placeholder="Descrição" required>
                        </div>
                        <div class="col-md-2 d-none" id="lancamento-km-wrapper">
                            <input type="number" name="km_veiculo" class="form-control form-control-sm"
                                   placeholder="KM do veículo" min="0">
                        </div>
                        <div class="col-md-2 d-none" id="lancamento-litros-wrapper">
                            <input type="number" name="litros" class="form-control form-control-sm"
                                   placeholder="Litros" step="0.01" min="0">
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="valor" class="form-control form-control-sm"
                                   placeholder="Valor" step="0.01" min="0" required>
                        </div>
                        <input type="hidden" name="data_lancamento" value="{{ date('Y-m-d') }}">
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-warning btn-sm w-100">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            @endif
        </div>

        @push('scripts')
        <script>
        (function () {
            const tipo = document.getElementById('lancamento-tipo');
            const kmWrapper = document.getElementById('lancamento-km-wrapper');
            const litrosWrapper = document.getElementById('lancamento-litros-wrapper');
            if (!tipo || !kmWrapper || !litrosWrapper) return;

            tipo.addEventListener('change', function () {
                const ehCombustivel = tipo.value === 'combustivel';
                kmWrapper.classList.toggle('d-none', !ehCombustivel);
                litrosWrapper.classList.toggle('d-none', !ehCombustivel);
            });
        })();
        </script>
        @endpush

        {{-- Resumo Financeiro --}}
        <div class="card border-0 mb-4" style="background: linear-gradient(135deg,#1a1a2e,#16213e)">
            <div class="card-header border-0" style="background:transparent">
                <span class="text-white fw-semibold">
                    <i class="bi bi-graph-up me-2"></i>Resumo Financeiro
                </span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                <table class="table table-sm mb-0" style="color:rgba(255,255,255,.8)">
                    <tr>
                        <td>Valor do Frete</td>
                        <td class="text-end fw-semibold text-white">
                            R$ {{ number_format($viagem->valor_frete, 2, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td>(-) Comissão Motorista</td>
                        <td class="text-end" style="color:#f97316">
                            R$ {{ number_format($viagem->valor_motorista, 2, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td>(-) Combustível</td>
                        <td class="text-end" style="color:#f97316">
                            R$ {{ number_format($viagem->total_combustivel, 2, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td>(-) Manutenção</td>
                        <td class="text-end" style="color:#f97316">
                            R$ {{ number_format($viagem->total_manutencao, 2, ',', '.') }}
                        </td>
                    </tr>
                    <tr style="border-top:1px solid rgba(255,255,255,.2)">
                        <td class="fw-bold text-white pt-2">Lucro da Transportadora</td>
                        <td class="text-end fw-bold pt-2
                            {{ $viagem->lucro_transportadora >= 0 ? 'text-success' : 'text-danger' }}">
                            R$ {{ number_format($viagem->lucro_transportadora, 2, ',', '.') }}
                        </td>
                    </tr>
                    <tr style="border-top:1px solid rgba(255,255,255,.2)">
                        <td class="text-white pt-2">Comissão Motorista</td>
                        <td class="text-end text-white pt-2">
                            R$ {{ number_format($viagem->valor_motorista, 2, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td>(-) Descontos</td>
                        <td class="text-end text-danger">
                            R$ {{ number_format($viagem->total_descontos, 2, ',', '.') }}
                        </td>
                    </tr>
                    @if($viagem->total_bonificacoes > 0)
                    <tr>
                        <td>(+) Bonificações</td>
                        <td class="text-end text-success">
                            R$ {{ number_format($viagem->total_bonificacoes, 2, ',', '.') }}
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td>(-) Adiantamento
                            @if($viagem->valor_adiantamento > 0 && !$viagem->adiantamento_descontavel)
                                <span class="badge bg-info ms-1" style="font-size:.65rem">Não descontado</span>
                            @endif
                        </td>
                        <td class="text-end text-danger">
                            R$ {{ number_format($viagem->adiantamento_descontavel ? $viagem->valor_adiantamento : 0, 2, ',', '.') }}
                        </td>
                    </tr>
                    <tr style="border-top:1px solid rgba(255,255,255,.2)">
                        <td class="fw-bold text-white pt-2">Saldo a Pagar Motorista</td>
                        <td class="text-end fw-bold pt-2
                            {{ $viagem->saldo_motorista >= 0 ? 'text-success' : 'text-danger' }}">
                            R$ {{ number_format($viagem->saldo_motorista, 2, ',', '.') }}
                        </td>
                    </tr>
                </table>
                </div>
            </div>
        </div>

        {{-- Assinatura Digital do Motorista --}}
        <div class="card mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-pen me-2 text-primary"></i>Assinatura Digital do Motorista
            </div>
            <div class="card-body">
                @if($viagem->assinatura_motorista_path)
                    <img src="{{ $viagem->assinatura_motorista_url }}" alt="Assinatura do motorista"
                         style="max-width:100%;max-height:120px;border:1px solid #e9ecef;border-radius:8px;background:#fff">
                    <div class="text-muted small mt-2">
                        <i class="bi bi-check-circle-fill text-success me-1"></i>
                        Assinado em {{ $viagem->assinatura_motorista_em->format('d/m/Y \à\s H:i') }}
                    </div>
                    @if($viagem->podeSerAssinada())
                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2"
                            data-bs-toggle="modal" data-bs-target="#modalAssinatura">
                        <i class="bi bi-arrow-repeat me-1"></i> Assinar Novamente
                    </button>
                    @endif
                @elseif($viagem->podeSerAssinada())
                    <p class="text-muted small mb-2">O motorista ainda não assinou o comprovante de acerto desta viagem.</p>
                    <button type="button" class="btn btn-primary btn-sm"
                            data-bs-toggle="modal" data-bs-target="#modalAssinatura">
                        <i class="bi bi-pen me-1"></i> Coletar Assinatura
                    </button>
                @else
                    <p class="text-muted small mb-0">A assinatura fica disponível quando a viagem estiver aguardando acerto ou encerrada.</p>
                @endif
            </div>
        </div>

    </div>
</div>

{{-- Modal de Assinatura --}}
<div class="modal fade" id="modalAssinatura" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title mb-0"><i class="bi bi-pen me-2"></i>Assinatura do Motorista</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">
                    Peça para <strong>{{ $viagem->motorista->nome }}</strong> assinar no espaço abaixo, confirmando o acerto desta viagem.
                </p>
                <canvas id="assinatura-canvas" width="460" height="180"
                        style="border:1px solid #ced4da;border-radius:8px;width:100%;touch-action:none;cursor:crosshair;background:#fff"></canvas>
                <div class="text-end mt-2">
                    <button type="button" id="btn-limpar-assinatura" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-eraser me-1"></i> Limpar
                    </button>
                </div>
                <div class="text-danger small mt-1" id="erro-assinatura" style="display:none">
                    Colete a assinatura antes de confirmar.
                </div>
                <form id="form-assinatura" action="{{ route('viagens.assinar', $viagem) }}" method="POST">
                    @csrf @method('PATCH')
                    <input type="hidden" name="assinatura" id="input-assinatura">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btn-confirmar-assinatura" class="btn btn-primary btn-sm">
                    <i class="bi bi-check-lg me-1"></i> Confirmar Assinatura
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const canvas = document.getElementById('assinatura-canvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    let desenhando = false;
    let temTraco = false;

    function limparCanvas() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        temTraco = false;
        document.getElementById('erro-assinatura').style.display = 'none';
    }

    function posicao(e) {
        const rect = canvas.getBoundingClientRect();
        return {
            x: (e.clientX - rect.left) * (canvas.width / rect.width),
            y: (e.clientY - rect.top) * (canvas.height / rect.height),
        };
    }

    canvas.addEventListener('pointerdown', function (e) {
        desenhando = true;
        temTraco = true;
        const p = posicao(e);
        ctx.beginPath();
        ctx.moveTo(p.x, p.y);
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.strokeStyle = '#1a1a2e';
    });

    canvas.addEventListener('pointermove', function (e) {
        if (!desenhando) return;
        const p = posicao(e);
        ctx.lineTo(p.x, p.y);
        ctx.stroke();
    });

    ['pointerup', 'pointerleave', 'pointercancel'].forEach(function (evento) {
        canvas.addEventListener(evento, function () { desenhando = false; });
    });

    document.getElementById('btn-limpar-assinatura').addEventListener('click', limparCanvas);
    document.getElementById('modalAssinatura').addEventListener('shown.bs.modal', limparCanvas);

    document.getElementById('btn-confirmar-assinatura').addEventListener('click', function () {
        if (!temTraco) {
            document.getElementById('erro-assinatura').style.display = 'block';
            return;
        }
        document.getElementById('input-assinatura').value = canvas.toDataURL('image/png');
        document.getElementById('form-assinatura').submit();
    });
})();
</script>
@endpush
@endsection