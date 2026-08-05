@extends('layouts.portal')
@section('title', 'Minhas Viagens')

@section('content')
<div class="mb-4">
    <h4 class="mb-0">Minhas Viagens</h4>
    <small class="text-muted">Acompanhe suas viagens e acertos</small>
</div>

@foreach($programacoesPendentes as $programacao)
<div class="card mb-3 border-start border-primary border-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <div class="fw-semibold">
                    <i class="bi bi-signpost-2 me-1 text-primary"></i>
                    Próxima coleta: {{ $programacao->origem }} → {{ $programacao->destino }}
                </div>
                <div class="text-muted small">
                    {{ $programacao->data_prevista->format('d/m/Y') }}
                    @if($programacao->hora_coleta)
                        às {{ \Illuminate\Support\Carbon::parse($programacao->hora_coleta)->format('H:i') }}
                    @endif
                </div>
            </div>

            @if($programacao->chegadaInformada())
                <span class="badge bg-success-subtle text-success">
                    <i class="bi bi-check-circle me-1"></i>
                    Chegada informada às {{ $programacao->chegada_horario_informado->format('H:i') }}
                </span>
            @else
                <form action="{{ route('portal.programacoes.chegada', $programacao) }}" method="POST"
                      class="d-flex gap-2 align-items-center">
                    @csrf
                    <input type="time" name="horario" class="form-control form-control-sm"
                           style="width:100px" value="{{ now()->format('H:i') }}">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-geo-alt me-1"></i> Cheguei no local de coleta
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endforeach

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Rota</th>
                    <th>Saída</th>
                    <th>Frete</th>
                    <th>Saldo</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($viagens as $viagem)
                <tr>
                    <td class="ps-4">{{ $viagem->origem }} → {{ $viagem->destino }}</td>
                    <td>{{ $viagem->data_saida->format('d/m/Y') }}</td>
                    <td>R$ {{ number_format($viagem->valor_frete, 2, ',', '.') }}</td>
                    <td class="{{ $viagem->saldo_motorista >= 0 ? 'text-success' : 'text-danger' }} fw-semibold">
                        R$ {{ number_format($viagem->saldo_motorista, 2, ',', '.') }}
                    </td>
                    <td>
                        <span class="badge badge-status-{{ $viagem->status }}">
                            {{ ucfirst(str_replace('_', ' ', $viagem->status)) }}
                        </span>
                    </td>
                    <td class="text-end pe-4">
                        <a href="{{ route('portal.viagens.show', $viagem) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i> Ver
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="bi bi-truck fs-3 d-block mb-2"></i>
                        Nenhuma viagem encontrada.
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
@endsection
