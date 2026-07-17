@extends('layouts.app')
@section('title', 'Cliente')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $cliente->nome }}</h4>
        <small class="text-muted">{{ $cliente->documento_formatado }}</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-1"></i> Editar
        </a>
        <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Voltar
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card mb-4 border-start border-primary border-3">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-person-vcard me-2 text-primary"></i>Dados do Cliente
            </div>
            <div class="card-body">
                <div class="table-responsive">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">Tipo</td>
                        <td>{{ $cliente->tipo_pessoa === 'juridica' ? 'Pessoa Jurídica' : 'Pessoa Física' }}</td></tr>
                    <tr><td class="text-muted">{{ $cliente->tipo_pessoa === 'juridica' ? 'CNPJ' : 'CPF' }}</td>
                        <td class="fw-semibold">
                            @if($cliente->tipo_pessoa === 'fisica')
                                <x-dado-sensivel :mascarado="$cliente->documento_mascarado" :completo="$cliente->documento_formatado" />
                            @else
                                {{ $cliente->documento_formatado }}
                            @endif
                        </td></tr>
                    @if($cliente->razao_social)
                    <tr><td class="text-muted">Razão Social</td><td>{{ $cliente->razao_social }}</td></tr>
                    @endif
                    @if($cliente->ie)
                    <tr><td class="text-muted">IE</td><td>{{ $cliente->ie }}</td></tr>
                    @endif
                    <tr><td class="text-muted">Telefone</td><td>{{ $cliente->telefone ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Celular</td><td>{{ $cliente->celular ?? '-' }}</td></tr>
                    <tr><td class="text-muted">E-mail</td><td>{{ $cliente->email ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Contato</td><td>{{ $cliente->contato ?? '-' }}</td></tr>
                    @if($cliente->tabela_frete)
                    <tr><td class="text-muted">Frete Padrão</td>
                        <td>R$ {{ number_format($cliente->tabela_frete, 2, ',', '.') }}/km</td></tr>
                    @endif
                    <tr><td class="text-muted">Status</td>
                        <td><span class="badge {{ $cliente->status === 'ativo' ? 'bg-success' : 'bg-secondary' }}">
                            {{ ucfirst($cliente->status) }}</span></td></tr>
                </table>
                </div>
                <div class="text-muted border-top pt-2 mt-2" style="font-size:.75rem">
                    <i class="bi bi-person-plus me-1"></i>Cadastrado por {{ $cliente->criadoPor?->name ?? 'desconhecido' }}
                </div>
            </div>
        </div>

        @if($cliente->logradouro)
        <div class="card border-start border-info border-3">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-geo-alt me-2 text-primary"></i>Endereço
            </div>
            <div class="card-body">
                <p class="mb-1">{{ $cliente->logradouro }}, {{ $cliente->numero }}</p>
                @if($cliente->complemento)
                    <p class="mb-1">{{ $cliente->complemento }}</p>
                @endif
                <p class="mb-1">{{ $cliente->bairro }}</p>
                <p class="mb-1">{{ $cliente->cidade }}/{{ $cliente->estado }}</p>
                <p class="mb-0 text-muted">CEP: {{ $cliente->cep }}</p>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-8">
        <div class="card border-start border-secondary border-3">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-truck me-2 text-primary"></i>Histórico de Viagens
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Motorista</th>
                            <th>Rota</th>
                            <th>Saída</th>
                            <th>Frete</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($viagens as $viagem)
                        <tr onclick="window.location='{{ route('viagens.show', $viagem) }}'"
                            style="cursor:pointer">
                            <td class="ps-3 text-muted">#{{ $viagem->id }}</td>
                            <td>{{ $viagem->motorista->nome }}</td>
                            <td class="small">{{ $viagem->origem }} → {{ $viagem->destino }}</td>
                            <td>{{ $viagem->data_saida->format('d/m/Y') }}</td>
                            <td>R$ {{ number_format($viagem->valor_frete, 2, ',', '.') }}</td>
                            <td><span class="badge badge-status-{{ $viagem->status }}">
                                {{ ucfirst(str_replace('_', ' ', $viagem->status)) }}</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Nenhuma viagem vinculada a este cliente.
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
    </div>
</div>
@endsection