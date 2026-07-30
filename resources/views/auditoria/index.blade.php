@extends('layouts.app')
@section('title', 'Auditoria')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Log de Auditoria</h4>
        <small class="text-muted">Quem criou, editou ou excluiu registros — consulta somente leitura, retida por {{ (int) round(config('activitylog.delete_records_older_than_days') / 365) }} anos</small>
    </div>
</div>

{{-- Filtros --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('auditoria.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Registro</label>
                    <select name="log_name" class="form-select">
                        <option value="">Todos</option>
                        @foreach($logNames as $nome)
                        <option value="{{ $nome }}" {{ request('log_name') === $nome ? 'selected' : '' }}>
                            {{ ucfirst($nome) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Ação</label>
                    <select name="event" class="form-select">
                        <option value="">Todas</option>
                        <option value="created" {{ request('event') === 'created' ? 'selected' : '' }}>Criação</option>
                        <option value="updated" {{ request('event') === 'updated' ? 'selected' : '' }}>Edição</option>
                        <option value="deleted" {{ request('event') === 'deleted' ? 'selected' : '' }}>Exclusão</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">De</label>
                    <input type="date" name="data_inicio" class="form-control" value="{{ request('data_inicio') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Até</label>
                    <input type="date" name="data_fim" class="form-control" value="{{ request('data_fim') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                </div>
                <div class="col-md-1">
                    <a href="{{ route('auditoria.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Data/Hora</th>
                    <th>Usuário</th>
                    <th>Ação</th>
                    <th>Registro</th>
                    <th>Alterações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($registros as $registro)
                <tr>
                    <td class="ps-4">{{ $registro->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $registro->causer?->name ?? $registro->causer?->nome ?? '—' }}</td>
                    <td>
                        @php
                            $badge = match($registro->event) {
                                'created' => 'success',
                                'updated' => 'primary',
                                'deleted' => 'danger',
                                default   => 'secondary',
                            };
                        @endphp
                        <span class="badge bg-{{ $badge }}-subtle text-{{ $badge }}-emphasis">
                            {{ ucfirst($registro->event ?? '—') }}
                        </span>
                    </td>
                    <td>{{ ucfirst($registro->log_name) }} #{{ $registro->subject_id }}</td>
                    <td class="small text-muted">
                        @php $alteracoes = $registro->changes()['attributes'] ?? []; @endphp
                        @if(count($alteracoes))
                            {{ collect($alteracoes)->keys()->implode(', ') }}
                        @else
                            —
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        <i class="bi bi-shield-check fs-3 d-block mb-2"></i>
                        Nenhum registro de auditoria no período.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
    @if($registros->hasPages())
    <div class="card-footer">{{ $registros->links() }}</div>
    @endif
</div>
@endsection
