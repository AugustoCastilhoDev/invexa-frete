@extends('layouts.app')
@section('title', 'Viagens')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Viagens</h4>
        <small class="text-muted">Gerencie todas as viagens</small>
    </div>
    <a href="{{ route('viagens.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Nova Viagem
    </a>
</div>

{{-- ── Filtros ── --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('viagens.index') }}">
            <div class="row g-3 align-items-end">

                {{-- Status --}}
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="todas"            {{ request('status','todas') === 'todas'             ? 'selected' : '' }}>Todos</option>
                        <option value="aberta"           {{ request('status') === 'aberta'                   ? 'selected' : '' }}>Aberta</option>
                        <option value="em_andamento"     {{ request('status') === 'em_andamento'             ? 'selected' : '' }}>Em Andamento</option>
                        <option value="aguardando_acerto"{{ request('status') === 'aguardando_acerto'        ? 'selected' : '' }}>Aguard. Acerto</option>
                        <option value="encerrada"        {{ request('status') === 'encerrada'                ? 'selected' : '' }}>Encerrada</option>
                    </select>
                </div>

                {{-- Motorista --}}
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Motorista</label>
                    <select name="motorista_id" class="form-select form-select-sm">
                        <option value="">Todos os motoristas</option>
                        @foreach($motoristas as $m)
                            <option value="{{ $m->id }}"
                                {{ request('motorista_id') == $m->id ? 'selected' : '' }}>
                                {{ $m->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Veículo --}}
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Veículo</label>
                    <select name="veiculo_id" class="form-select form-select-sm">
                        <option value="">Todos os veículos</option>
                        @foreach($veiculos as $v)
                            <option value="{{ $v->id }}"
                                {{ request('veiculo_id') == $v->id ? 'selected' : '' }}>
                                {{ $v->placa }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Data Início --}}
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Saída de</label>
                    <input type="date" name="data_inicio" class="form-control form-control-sm"
                           value="{{ request('data_inicio') }}">
                </div>

                {{-- Data Fim --}}
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Saída até</label>
                    <input type="date" name="data_fim" class="form-control form-control-sm"
                           value="{{ request('data_fim') }}">
                </div>

                {{-- Botões --}}
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-search"></i>
                    </button>
                    <a href="{{ route('viagens.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- ── Resumo dos filtros ativos ── --}}
@php
    $filtrosAtivos = array_filter([
        request('status', 'todas') !== 'todas' ? request('status') : null,
        request('motorista_id'),
        request('veiculo_id'),
        request('data_inicio'),
        request('data_fim'),
    ]);
@endphp

@if(count($filtrosAtivos))
<div class="alert alert-info py-2 d-flex justify-content-between align-items-center">
    <span class="small">
        <i class="bi bi-funnel me-1"></i>
        Filtros ativos — exibindo <strong>{{ $viagens->total() }}</strong> viagem(ns)
    </span>
    <a href="{{ route('viagens.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-x me-1"></i> Limpar filtros
    </a>
</div>
@endif

{{-- ── Tabela ── --}}
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">#</th>
                    <th>Motorista</th>
                    <th>Veículo</th>
                    <th>Cliente</th>
                    <th>Origem / Destino</th>
                    <th>Saída</th>
                    <th>Frete</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($viagens as $viagem)
                <tr>
                    <td class="ps-4 text-muted">#{{ $viagem->id }}</td>
                    <td class="fw-semibold">{{ $viagem->motorista->nome }}</td>
                    <td>{{ $viagem->veiculo->placa }}</td>
                    <td>{{ $viagem->cliente->nome ?? '-' }}</td>
                    <td>{{ $viagem->origem }} → {{ $viagem->destino }}</td>
                    <td>{{ $viagem->data_saida->format('d/m/Y') }}</td>
                    <td>R$ {{ number_format($viagem->valor_frete, 2, ',', '.') }}</td>
                    <td>
                        <span class="badge badge-status-{{ $viagem->status }}">
                            {{ ucfirst(str_replace('_', ' ', $viagem->status)) }}
                        </span>
                    </td>
                    <td class="text-end pe-4">
                        <a href="{{ route('viagens.show', $viagem) }}"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i>
                        </a>
                        @if($viagem->status !== 'encerrada')
                        <a href="{{ route('viagens.edit', $viagem) }}"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @endif
                        <form action="{{ route('viagens.destroy', $viagem) }}"
                              method="POST" class="d-inline"
                              onsubmit="return confirm('Confirma exclusão desta viagem?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                        <i class="bi bi-truck fs-3 d-block mb-2"></i>
                        Nenhuma viagem encontrada com os filtros selecionados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($viagens->hasPages())
    <div class="card-footer">{{ $viagens->links() }}</div>
    @endif
</div>
@endsection