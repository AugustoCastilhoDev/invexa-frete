@extends('layouts.portal')
@section('title', 'Minhas Viagens')

@section('content')
<div class="mb-4">
    <h4 class="mb-0">Minhas Viagens</h4>
    <small class="text-muted">Acompanhe suas viagens e acertos</small>
</div>

<div class="card">
    <div class="card-body p-0">
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
    @if($viagens->hasPages())
    <div class="card-footer">{{ $viagens->links() }}</div>
    @endif
</div>
@endsection
