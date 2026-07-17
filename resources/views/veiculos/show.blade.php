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
        <div class="card border-start border-primary border-3">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-car-front me-2 text-primary"></i>Dados do Veículo
            </div>
            <div class="card-body">
                <div class="table-responsive">
                <table class="table table-sm table-borderless">
                    <tr><td class="text-muted">Placa</td><td class="fw-semibold">{{ $veiculo->placa }}</td></tr>
                    <tr><td class="text-muted">Modelo</td><td>{{ $veiculo->modelo }}</td></tr>
                    <tr><td class="text-muted">Marca</td><td>{{ $veiculo->marca ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Ano</td><td>{{ $veiculo->ano ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Tipo</td><td>{{ ucfirst($veiculo->tipo) }}</td></tr>
                    <tr><td class="text-muted">RENAVAM</td><td>{{ $veiculo->renavam ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Chassi</td><td>{{ $veiculo->chassi ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Validade do Documento</td>
                        <td>{{ $veiculo->validade_documento?->format('d/m/Y') ?? '-' }}</td></tr>
                    @if($veiculo->tipo === 'carreta')
                    <tr><td class="text-muted">Cavalo Vinculado</td>
                        <td>{{ $veiculo->cavalo ? $veiculo->cavalo->placa . ' — ' . $veiculo->cavalo->modelo : '-' }}</td></tr>
                    @elseif($veiculo->tipo === 'truck')
                    <tr><td class="text-muted">Carreta(s) Vinculada(s)</td>
                        <td>{{ $veiculo->carretas->isNotEmpty() ? $veiculo->carretas->pluck('placa')->join(', ') : '-' }}</td></tr>
                    @endif
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
                </div>
                <div class="text-muted border-top pt-2 mt-2" style="font-size:.75rem">
                    <i class="bi bi-person-plus me-1"></i>Cadastrado por {{ $veiculo->criadoPor?->name ?? 'desconhecido' }}
                </div>
            </div>
        </div>
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
            </div>
            @if($viagens->hasPages())
            <div class="card-footer">{{ $viagens->links() }}</div>
            @endif
        </div>
    </div>
</div>

{{-- ── Manutenções ── --}}
<div class="row g-4 mt-1">
    <div class="col-12">
        <div class="card border-start border-warning border-3">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-tools me-2 text-warning"></i>Manutenções</span>
                <span class="text-warning fw-bold">
                    R$ {{ number_format($veiculo->manutencoes->sum('valor'), 2, ',', '.') }} gastos
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Tipo</th>
                            <th>Descrição</th>
                            <th>Data</th>
                            <th>KM</th>
                            <th>Valor</th>
                            <th>Próxima</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($veiculo->manutencoes as $manutencao)
                        <tr>
                            <td class="ps-3">
                                <span class="badge {{ $manutencao->tipo === 'preventiva' ? 'bg-info text-dark' : 'bg-danger' }}">
                                    {{ ucfirst($manutencao->tipo) }}
                                </span>
                            </td>
                            <td>{{ $manutencao->descricao }}</td>
                            <td>{{ $manutencao->data_manutencao->format('d/m/Y') }}</td>
                            <td>{{ $manutencao->km_veiculo ? number_format($manutencao->km_veiculo, 0, ',', '.') : '-' }}</td>
                            <td>R$ {{ number_format($manutencao->valor, 2, ',', '.') }}</td>
                            <td class="small text-muted">
                                @if($manutencao->proxima_manutencao_data)
                                    {{ $manutencao->proxima_manutencao_data->format('d/m/Y') }}
                                @elseif($manutencao->proxima_manutencao_km)
                                    {{ number_format($manutencao->proxima_manutencao_km, 0, ',', '.') }} km
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($manutencao->status === 'em_andamento')
                                    <form action="{{ route('manutencoes.update', $manutencao) }}" method="POST" class="d-inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="concluida">
                                        <button class="btn btn-sm btn-outline-success" title="Marcar como concluída">
                                            <i class="bi bi-check-lg"></i> Em andamento
                                        </button>
                                    </form>
                                @else
                                    <span class="badge bg-success">Concluída</span>
                                @endif
                            </td>
                            <td>
                                @if(auth()->user()?->isAdmin())
                                <form action="{{ route('manutencoes.destroy', $manutencao) }}" method="POST"
                                      onsubmit="return confirm('Remover este registro de manutenção?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-link text-danger p-0">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center text-muted py-3">Nenhuma manutenção registrada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                <form action="{{ route('manutencoes.store', $veiculo) }}" method="POST">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-2">
                            <select name="tipo" class="form-select form-select-sm" required>
                                <option value="">Tipo</option>
                                <option value="preventiva">Preventiva</option>
                                <option value="corretiva">Corretiva</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="descricao" class="form-control form-control-sm"
                                   placeholder="Descrição" required>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="data_manutencao" class="form-control form-control-sm"
                                   value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-1">
                            <input type="number" name="km_veiculo" class="form-control form-control-sm"
                                   placeholder="KM" min="0">
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="valor" class="form-control form-control-sm"
                                   placeholder="Valor" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-1">
                            <select name="status" class="form-select form-select-sm" required>
                                <option value="concluida">Concluída</option>
                                <option value="em_andamento">Em andamento</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-warning btn-sm w-100">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-0">Próxima manutenção (data)</label>
                            <input type="date" name="proxima_manutencao_data" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-0">Próxima manutenção (KM)</label>
                            <input type="number" name="proxima_manutencao_km" class="form-control form-control-sm" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted mb-0">Observação</label>
                            <input type="text" name="observacao" class="form-control form-control-sm">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection