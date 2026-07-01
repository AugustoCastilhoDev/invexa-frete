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
            <form action="{{ route('viagens.encerrar', $viagem) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Encerrar esta viagem?')">
                @csrf @method('PATCH')
                <button class="btn btn-success btn-sm">
                    <i class="bi bi-check-circle me-1"></i> Encerrar Viagem
                </button>
            </form>
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

        {{-- Descontos --}}
        <div class="card mb-4 border-start border-danger border-3">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-dash-circle me-2 text-danger"></i>Descontos</span>
                <span class="text-danger fw-bold">
                    R$ {{ number_format($viagem->total_descontos, 2, ',', '.') }}
                </span>
            </div>
            <div class="card-body p-0">
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
                        <tr>
                            <td class="ps-3">
                                <span class="badge bg-danger bg-opacity-10 text-danger">
                                    {{ ucfirst($desconto->tipo) }}
                                </span>
                            </td>
                            <td>
                                {{ $desconto->descricao }}
                                <br><small class="text-muted">por {{ $desconto->criadoPor?->name ?? '—' }}</small>
                            </td>
                            <td>{{ $desconto->data_desconto->format('d/m/Y') }}</td>
                            <td>R$ {{ number_format($desconto->valor, 2, ',', '.') }}</td>
                            <td>
                                @if($viagem->status !== 'encerrada')
                                <form action="{{ route('descontos.destroy', $desconto) }}"
                                      method="POST"
                                      onsubmit="return confirm('Remover desconto?')">
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
                            Nenhum desconto lançado.
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
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
                <span class="badge bg-secondary">{{ $viagem->documentos->count() }}</span>
            </div>
            <div class="card-body p-0">
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
                                    <a href="{{ asset('storage/'.$doc->arquivo) }}"
                                       target="_blank"
                                       class="btn btn-sm btn-outline-secondary"
                                       title="Baixar arquivo">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    @endif
                                    @if($viagem->status !== 'encerrada')
                                    <form action="{{ route('documentos.destroy', $doc) }}"
                                          method="POST"
                                          onsubmit="return confirm('Remover documento?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
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
                            </td>
                            <td>{{ $lancamento->data_lancamento->format('d/m/Y') }}</td>
                            <td>R$ {{ number_format($lancamento->valor, 2, ',', '.') }}</td>
                            <td>
                                @if($viagem->status !== 'encerrada')
                                <form action="{{ route('lancamentos.destroy', $lancamento) }}"
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
                            Nenhum lançamento registrado.
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($viagem->status !== 'encerrada')
            <div class="card-footer bg-white">
                <form action="{{ route('lancamentos.store', $viagem) }}"
                      method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-4">
                            <select name="tipo" class="form-select form-select-sm" required>
                                <option value="">Tipo</option>
                                <option value="combustivel">Combustível</option>
                                <option value="manutencao">Manutenção</option>
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

        {{-- Resumo Financeiro --}}
        <div class="card border-0 mb-4" style="background: linear-gradient(135deg,#1a1a2e,#16213e)">
            <div class="card-header border-0" style="background:transparent">
                <span class="text-white fw-semibold">
                    <i class="bi bi-graph-up me-2"></i>Resumo Financeiro
                </span>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0" style="color:rgba(255,255,255,.8)">
                    <tr>
                        <td>Valor do Frete</td>
                        <td class="text-end fw-semibold text-white">
                            R$ {{ number_format($viagem->valor_frete, 2, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td>(-) Comissão Motorista</td>
                        <td class="text-end text-warning">
                            R$ {{ number_format($viagem->valor_motorista, 2, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td>(-) Combustível</td>
                        <td class="text-end text-warning">
                            R$ {{ number_format($viagem->total_combustivel, 2, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td>(-) Manutenção</td>
                        <td class="text-end text-warning">
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
</div>
@endsection