@extends('layouts.app')
@section('title', 'Veículo')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $veiculo->placa }} — {{ $veiculo->modelo }}</h4>
        <small class="text-muted">Detalhes do veículo</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('veiculos.edit', $veiculo) }}" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-1"></i> Editar
        </a>
        <a href="{{ route('veiculos.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Voltar
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-car-front me-2 text-primary"></i>Dados do Veículo
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr><td class="text-muted">Placa</td><td class="fw-semibold">{{ $veiculo->placa }}</td></tr>
                    <tr><td class="text-muted">Modelo</td><td>{{ $veiculo->modelo }}</td></tr>
                    <tr><td class="text-muted">Marca</td><td>{{ $veiculo->marca ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Ano</td><td>{{ $veiculo->ano ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Tipo</td><td>{{ ucfirst($veiculo->tipo) }}</td></tr>
                    <tr><td class="text-muted">RENAVAM</td><td>{{ $veiculo->renavam ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Capacidade</td>
                        <td>{{ $veiculo->capacidade_kg
                            ? number_format($veiculo->capacidade_kg, 0, ',', '.').' kg'
                            : '-' }}</td></tr>
                    <tr><td class="text-muted">Status</td>
                        <td>
                            @php
                                $badge = match($veiculo->status) {
                                    'ativo'      => 'bg-success',
                                    'inativo'    => 'bg-secondary',
                                    'manutencao' => 'bg-warning text-dark',
                                };
                            @endphp
                            <span class="badge {{ $badge }}">{{ ucfirst($veiculo->status) }}</span>
                        </td>
                    </tr>
                </table>
                <div class="text-muted border-top pt-2 mt-2" style="font-size:.75rem">
                    <i class="bi bi-person-plus me-1"></i>Cadastrado por {{ $veiculo->criadoPor?->name ?? 'desconhecido' }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-truck me-2 text-primary"></i>Histórico de Viagens
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Motorista</th>
                            <th>Origem / Destino</th>
                            <th>Saída</th>
                            <th>Frete</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($viagens as $viagem)
                        <tr onclick="window.location='{{ route('viagens.show', $viagem) }}'"
                            style="cursor:pointer">
                            <td class="ps-3">{{ $viagem->motorista->nome }}</td>
                            <td>{{ $viagem->origem }} → {{ $viagem->destino }}</td>
                            <td>{{ $viagem->data_saida->format('d/m/Y') }}</td>
                            <td>R$ {{ number_format($viagem->valor_frete, 2, ',', '.') }}</td>
                            <td><span class="badge badge-status-{{ $viagem->status }}">
                                {{ ucfirst(str_replace('_',' ',$viagem->status)) }}</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">
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
    </div>
</div>
@endsection