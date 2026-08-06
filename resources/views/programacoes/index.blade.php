@extends('layouts.app')
@section('title', 'Programação de Frota')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Programação de Frota</h4>
        <small class="text-muted">Planeje a próxima viagem de cada motorista/veículo antes de encerrar a atual</small>
    </div>
    <a href="{{ route('programacoes.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Nova Programação
    </a>
</div>

@php
    // Toggle de "Risco de No-Show": clica de novo pra tirar o filtro, preservando os demais.
    $queryAtual = collect(request()->query())->except('risco_no_show')->toArray();
    $urlToggleRisco = $riscoNoShow
        ? route('programacoes.index', $queryAtual)
        : route('programacoes.index', array_merge($queryAtual, ['risco_no_show' => 1]));
@endphp

{{-- ── Cards de Controle de Frota ── --}}
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card text-center border-start border-primary border-3">
            <div class="card-body py-3">
                <div class="text-muted small">Programações Pendentes</div>
                <div class="fs-3 fw-bold text-primary">{{ $totalPendentes }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center border-start border-warning border-3" role="button"
             data-bs-toggle="collapse" data-bs-target="#painelSemProgramacao"
             aria-expanded="false" aria-controls="painelSemProgramacao" style="cursor:pointer">
            <div class="card-body py-3">
                <div class="d-flex align-items-center justify-content-center gap-1 mb-1">
                    <i class="bi bi-exclamation-triangle text-warning" style="font-size:.8rem"></i>
                    <span class="text-muted small">Veículos Sem Próxima Viagem</span>
                </div>
                <div class="fw-bold text-warning fs-3">{{ $totalVeiculosSemProgramacao }}</div>
                <div class="text-muted" style="font-size:.7rem">clique pra ver quais e programar</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <a href="{{ $urlToggleRisco }}"
           class="card text-center border-start border-danger border-3 text-decoration-none {{ $riscoNoShow ? 'bg-danger-subtle' : '' }}">
            <div class="card-body py-3">
                <div class="d-flex align-items-center justify-content-center gap-1 mb-1">
                    <i class="bi bi-clock-history text-danger" style="font-size:.8rem"></i>
                    <span class="text-muted small">Risco de No-Show</span>
                </div>
                <div class="fw-bold text-danger fs-3">{{ $totalRiscoNoShow }}</div>
                <div class="text-muted" style="font-size:.7rem">
                    {{ $riscoNoShow ? 'filtrando — clique pra limpar' : 'coleta em ≤2h sem chegada informada' }}
                </div>
            </div>
        </a>
    </div>
</div>

{{-- ── Veículos sem próxima viagem (drill-down) ── --}}
<div class="collapse mb-4" id="painelSemProgramacao">
    <div class="card border-start border-warning border-3">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-exclamation-triangle me-2 text-warning"></i>
            Veículos ativos sem próxima viagem planejada
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Placa</th>
                        <th>Modelo</th>
                        <th class="text-end pe-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($veiculosSemProgramacao as $veiculo)
                    <tr>
                        <td class="ps-4 fw-semibold">{{ $veiculo->placa }}</td>
                        <td>{{ $veiculo->modelo }}</td>
                        <td class="text-end pe-4">
                            <a href="{{ route('programacoes.create', ['veiculo_id' => $veiculo->id]) }}"
                               class="btn btn-sm btn-primary">
                                <i class="bi bi-signpost-2 me-1"></i> Programar
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-3">
                            Todos os veículos ativos já têm próxima viagem planejada.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>

{{-- ── Filtros ── --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('programacoes.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="pendente"  {{ request('status', 'pendente') === 'pendente'  ? 'selected' : '' }}>Pendente</option>
                        <option value="confirmada"{{ request('status') === 'confirmada'            ? 'selected' : '' }}>Confirmada</option>
                        <option value="todas"     {{ request('status') === 'todas'                 ? 'selected' : '' }}>Todas</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Motorista</label>
                    <select name="motorista_id" class="form-select form-select-sm">
                        <option value="">Todos os motoristas</option>
                        @foreach($motoristas as $m)
                            <option value="{{ $m->id }}" {{ request('motorista_id') == $m->id ? 'selected' : '' }}>
                                {{ $m->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Veículo</label>
                    <select name="veiculo_id" class="form-select form-select-sm">
                        <option value="">Todos os veículos</option>
                        @foreach($veiculos as $v)
                            <option value="{{ $v->id }}" {{ request('veiculo_id') == $v->id ? 'selected' : '' }}>
                                {{ $v->placa }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Período</label>
                    <select name="periodo" class="form-select form-select-sm">
                        <option value=""       {{ request('periodo', '') === ''       ? 'selected' : '' }}>Todas as datas</option>
                        <option value="hoje"   {{ request('periodo') === 'hoje'       ? 'selected' : '' }}>Hoje</option>
                        <option value="amanha" {{ request('periodo') === 'amanha'     ? 'selected' : '' }}>Amanhã</option>
                        <option value="semana" {{ request('periodo') === 'semana'     ? 'selected' : '' }}>Esta semana</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-search"></i>
                    </button>
                    <a href="{{ route('programacoes.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </div>
            @if($riscoNoShow)
                <input type="hidden" name="risco_no_show" value="1">
                <div class="mt-2">
                    <span class="badge bg-danger-subtle text-danger">
                        <i class="bi bi-clock-history me-1"></i>Filtrando por risco de no-show
                    </span>
                </div>
            @endif
        </form>
    </div>
</div>

{{-- ── Tabela ── --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">#</th>
                    <th>Motorista</th>
                    <th>Veículo</th>
                    <th>Cliente</th>
                    <th>Origem / Destino</th>
                    <th>Data Prevista</th>
                    <th>Coleta</th>
                    <th>Frete</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($programacoes as $programacao)
                <tr>
                    <td class="ps-4 text-muted">#{{ $programacao->id }}</td>
                    <td class="fw-semibold">{{ $programacao->motorista->nome }}</td>
                    <td>
                        {{ $programacao->veiculo->placa }}
                        @if($programacao->carreta)
                            <div class="text-muted" style="font-size:.75rem">+ {{ $programacao->carreta->placa }}</div>
                        @endif
                    </td>
                    <td>{{ $programacao->cliente->nome ?? '-' }}</td>
                    <td>{{ $programacao->origem }} → {{ $programacao->destino }}</td>
                    <td>{{ $programacao->data_prevista->format('d/m/Y') }}</td>
                    <td>
                        @if(! $programacao->hora_coleta)
                            <span class="text-muted">-</span>
                        @else
                            <div>{{ \Illuminate\Support\Carbon::parse($programacao->hora_coleta)->format('H:i') }}</div>
                            @if($programacao->chegadaInformada())
                                <span class="badge bg-success-subtle text-success" style="font-size:.7rem">
                                    <i class="bi bi-check-circle me-1"></i>Chegada {{ $programacao->chegada_horario_informado->format('H:i') }}
                                </span>
                            @elseif($programacao->em_risco_de_no_show)
                                <span class="badge bg-danger-subtle text-danger" style="font-size:.7rem">
                                    <i class="bi bi-exclamation-triangle me-1"></i>Risco de no-show
                                </span>
                            @endif
                            @if($programacao->estaPendente() && ! $programacao->chegadaInformada())
                                <form action="{{ route('programacoes.chegada', $programacao) }}" method="POST"
                                      class="d-flex gap-1 align-items-center mt-1">
                                    @csrf
                                    <input type="time" name="horario" class="form-control form-control-sm"
                                           style="width:85px;font-size:.75rem" value="{{ now()->format('H:i') }}">
                                    <button type="submit" class="btn btn-sm btn-outline-success px-1 py-0"
                                            title="Confirmar chegada no local de coleta" style="font-size:.75rem">
                                        <i class="bi bi-geo-alt"></i>
                                    </button>
                                </form>
                            @endif
                        @endif
                    </td>
                    <td>{{ $programacao->valor_frete !== null ? 'R$ ' . number_format($programacao->valor_frete, 2, ',', '.') : '-' }}</td>
                    <td>
                        @if($programacao->status === 'pendente')
                            <span class="badge bg-warning-subtle text-warning">Pendente</span>
                        @else
                            <span class="badge bg-success-subtle text-success">Confirmada</span>
                        @endif
                    </td>
                    <td class="text-end pe-4">
                        @if($programacao->estaPendente())
                            <a href="{{ route('viagens.create', [
                                    'programacao_id' => $programacao->id,
                                    'motorista_id'   => $programacao->motorista_id,
                                    'veiculo_id'     => $programacao->veiculo_id,
                                    'carreta_id'     => $programacao->carreta_id,
                                    'cliente_id'     => $programacao->cliente_id,
                                    'origem'         => $programacao->origem,
                                    'origem_uf'      => $programacao->origem_uf,
                                    'origem_codigo_municipio' => $programacao->origem_codigo_municipio,
                                    'destino'        => $programacao->destino,
                                    'destino_uf'     => $programacao->destino_uf,
                                    'destino_codigo_municipio' => $programacao->destino_codigo_municipio,
                                    'valor_frete'    => $programacao->valor_frete,
                                ]) }}"
                               class="btn btn-sm btn-success" title="Confirmar e Abrir Viagem">
                                <i class="bi bi-check-circle me-1"></i> Confirmar
                            </a>
                            <a href="{{ route('programacoes.edit', $programacao) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('programacoes.destroy', $programacao) }}"
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Excluir esta programação?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        @else
                            <a href="{{ route('viagens.show', $programacao->viagem_id) }}"
                               class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye me-1"></i> Ver Viagem
                            </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center text-muted py-5">
                        <i class="bi bi-signpost-2 fs-3 d-block mb-2"></i>
                        Nenhuma programação encontrada.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
    @if($programacoes->hasPages())
    <div class="card-footer">{{ $programacoes->links() }}</div>
    @endif
</div>
@endsection
