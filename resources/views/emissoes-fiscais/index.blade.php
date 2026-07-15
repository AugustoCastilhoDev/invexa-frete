@extends('layouts.app')
@section('title', 'Emissões Fiscais')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Emissões Fiscais</h4>
        <small class="text-muted">Todos os CT-e e MDF-e emitidos pela frota, em um só lugar</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('emissoes-fiscais.csv', request()->query()) }}" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Exportar CSV
        </a>
    </div>
</div>

{{-- ── Filtros ── --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('emissoes-fiscais.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Tipo</label>
                    <select name="tipo" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="cte" {{ request('tipo') === 'cte' ? 'selected' : '' }}>CT-e</option>
                        <option value="mdfe" {{ request('tipo') === 'mdfe' ? 'selected' : '' }}>MDF-e</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="processando_autorizacao" {{ request('status') === 'processando_autorizacao' ? 'selected' : '' }}>Processando</option>
                        <option value="autorizado" {{ request('status') === 'autorizado' ? 'selected' : '' }}>Autorizado</option>
                        <option value="encerrado" {{ request('status') === 'encerrado' ? 'selected' : '' }}>Encerrado</option>
                        <option value="erro_autorizacao" {{ request('status') === 'erro_autorizacao' ? 'selected' : '' }}>Erro na autorização</option>
                        <option value="erro_encerramento" {{ request('status') === 'erro_encerramento' ? 'selected' : '' }}>Erro no encerramento</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Veículo</label>
                    <select name="veiculo_id" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        @foreach($veiculos as $v)
                            <option value="{{ $v->id }}"
                                {{ request('veiculo_id') == $v->id ? 'selected' : '' }}>
                                {{ $v->placa }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Data Início</label>
                    <input type="date" name="data_inicio" class="form-control form-control-sm"
                           value="{{ request('data_inicio') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Data Fim</label>
                    <input type="date" name="data_fim" class="form-control form-control-sm"
                           value="{{ request('data_fim') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ── Card Totalizador ── --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-center border-start border-primary border-3">
            <div class="card-body py-3">
                <div class="text-muted small">Registros</div>
                <div class="fs-3 fw-bold text-primary">{{ $totalRegistros }}</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Listagem ── --}}
<div class="card border-start border-secondary border-3">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-file-earmark-check me-2 text-primary"></i>Emissões
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Viagem</th>
                    <th>Tipo</th>
                    <th>Número / Série</th>
                    <th>Status</th>
                    <th>Emitido em</th>
                    <th>Encerrado em</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($emissoes as $emissao)
                <tr>
                    <td class="ps-3">
                        <a href="{{ route('viagens.show', $emissao->viagem_id) }}" class="fw-semibold text-decoration-none">
                            Viagem #{{ $emissao->viagem_id }}
                        </a>
                        <br>
                        <small class="text-muted">
                            {{ $emissao->viagem?->veiculo?->placa ?? '-' }} —
                            {{ $emissao->viagem?->motorista?->nome ?? '-' }}
                        </small>
                    </td>
                    <td>
                        <span class="badge {{ $emissao->tipo === 'mdfe' ? 'bg-info text-dark' : 'bg-primary' }} bg-opacity-10 text-{{ $emissao->tipo === 'mdfe' ? 'info' : 'primary' }}">
                            {{ $emissao->tipo_formatado }}
                        </span>
                    </td>
                    <td>{{ $emissao->numero ?? '-' }} @if($emissao->serie) / {{ $emissao->serie }} @endif</td>
                    <td>
                        @php
                            $corStatus = match($emissao->status) {
                                'autorizado' => 'success',
                                'encerrado' => 'secondary',
                                'processando_autorizacao' => 'warning',
                                default => 'danger',
                            };
                        @endphp
                        <span class="badge bg-{{ $corStatus }}">
                            {{ ucfirst(str_replace('_', ' ', $emissao->status)) }}
                        </span>
                    </td>
                    <td class="small text-muted">{{ $emissao->created_at->format('d/m/Y H:i') }}</td>
                    <td class="small text-muted">
                        {{ $emissao->encerrado_em?->format('d/m/Y H:i') ?? '-' }}
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            @unless($emissao->isFinal())
                            <form action="{{ route('emissoes-fiscais.atualizar-status', $emissao) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Atualizar status">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </form>
                            @endunless
                            @if($emissao->podeEncerrar())
                            <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal"
                                    data-bs-target="#encerrarMdfeIndex{{ $emissao->id }}" title="Encerrar MDF-e">
                                <i class="bi bi-flag"></i>
                            </button>
                            <div class="modal fade" id="encerrarMdfeIndex{{ $emissao->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('emissoes-fiscais.encerrar', $emissao) }}" method="POST">
                                            @csrf
                                            <div class="modal-header">
                                                <h6 class="modal-title mb-0">Encerrar MDF-e nº {{ $emissao->numero }}</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-2">
                                                    <label class="form-label small fw-semibold">Data do encerramento</label>
                                                    <input type="date" name="data" class="form-control form-control-sm"
                                                           value="{{ now()->toDateString() }}" required>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label small fw-semibold">UF do encerramento</label>
                                                    <select name="sigla_uf" class="form-select form-select-sm" required>
                                                        @foreach(['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'] as $uf)
                                                            <option value="{{ $uf }}">{{ $uf }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label small fw-semibold">Município do encerramento</label>
                                                    <input type="text" name="nome_municipio" class="form-control form-control-sm"
                                                           value="{{ $emissao->viagem?->destino }}" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-warning btn-sm">Encerrar MDF-e</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        Nenhuma emissão fiscal encontrada.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
    @if($emissoes->hasPages())
    <div class="card-footer bg-white py-2">
        {{ $emissoes->onEachSide(1)->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

@endsection
