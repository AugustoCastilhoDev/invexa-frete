@extends('layouts.app')
@section('title', 'Motorista')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $motorista->nome }}</h4>
        <small class="text-muted">Detalhes do motorista</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('motoristas.edit', $motorista) }}" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-1"></i> Editar
        </a>
        <a href="{{ route('motoristas.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Voltar
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-person-badge me-2 text-primary"></i>Dados Pessoais
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr><td class="text-muted">CPF</td>
                        <td><x-dado-sensivel :mascarado="$motorista->cpf_mascarado" :completo="$motorista->cpf" /></td></tr>
                    <tr><td class="text-muted">CNH</td>
                        <td><x-dado-sensivel :mascarado="$motorista->cnh_mascarada" :completo="$motorista->cnh" /></td></tr>
                    <tr><td class="text-muted">Categoria</td><td>{{ $motorista->categoria_cnh ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Validade CNH</td>
                        <td>{{ $motorista->validade_cnh?->format('d/m/Y') ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Telefone</td><td>{{ $motorista->telefone ?? '-' }}</td></tr>
                    <tr><td class="text-muted">E-mail</td><td>{{ $motorista->email ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Comissão</td>
                        <td><strong>{{ number_format($motorista->percentual_comissao, 2, ',', '.') }}%</strong></td></tr>
                    <tr><td class="text-muted">Status</td>
                        <td><span class="badge {{ $motorista->status === 'ativo' ? 'bg-success' : 'bg-secondary' }}">
                            {{ ucfirst($motorista->status) }}</span></td></tr>
                </table>
                <div class="text-muted border-top pt-2 mt-2" style="font-size:.75rem">
                    <i class="bi bi-person-plus me-1"></i>Cadastrado por {{ $motorista->criadoPor?->name ?? 'desconhecido' }}
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
                            <th class="ps-3">Origem / Destino</th>
                            <th>Saída</th>
                            <th>Frete</th>
                            <th>Comissão</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($viagens as $viagem)
                        <tr onclick="window.location='{{ route('viagens.show', $viagem) }}'"
                            style="cursor:pointer">
                            <td class="ps-3">{{ $viagem->origem }} → {{ $viagem->destino }}</td>
                            <td>{{ $viagem->data_saida->format('d/m/Y') }}</td>
                            <td>R$ {{ number_format($viagem->valor_frete, 2, ',', '.') }}</td>
                            <td>R$ {{ number_format($viagem->valor_motorista, 2, ',', '.') }}</td>
                            <td><span class="badge badge-status-{{ $viagem->status }}">
                                {{ ucfirst(str_replace('_', ' ', $viagem->status)) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Nenhuma viagem encontrada.</td></tr>
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