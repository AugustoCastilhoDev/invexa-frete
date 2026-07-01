@extends('layouts.app')
@section('title', 'Acertos por Motorista')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Acertos por Motorista</h4>
        <small class="text-muted">Histórico financeiro individual</small>
    </div>
    @if($motoristaSel && $viagens->count() > 0)
    <a href="{{ route('acertos.pdf', request()->query()) }}"
       target="_blank" class="btn btn-outline-dark">
        <i class="bi bi-printer me-1"></i> Exportar PDF
    </a>
    @endif
</div>

{{-- ── Filtros ── --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('acertos.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Motorista *</label>
                    <select name="motorista_id" class="form-select" required>
                        <option value="">Selecione o motorista</option>
                        @foreach($motoristas as $m)
                            <option value="{{ $m->id }}"
                                {{ $motoristaSel == $m->id ? 'selected' : '' }}>
                                {{ $m->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Período de</label>
                    <input type="date" name="data_inicio" class="form-control"
                           value="{{ $dataInicio }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Até</label>
                    <input type="date" name="data_fim" class="form-control"
                           value="{{ $dataFim }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i> Buscar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@if($motorista)

    {{-- ── Perfil do Motorista ── --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-1 text-center">
                    <div style="width:55px;height:55px;border-radius:50%;background:linear-gradient(135deg,#1a1a2e,#f97316);
                                display:flex;align-items:center;justify-content:center;margin:0 auto">
                        <i class="bi bi-person-fill text-white fs-4"></i>
                    </div>
                </div>
                <div class="col-md-5">
                    <h5 class="mb-0 fw-bold">{{ $motorista->nome }}</h5>
                    <small class="text-muted">
                        CPF: {{ $motorista->cpf }}
                        @if($motorista->cnh) | CNH: {{ $motorista->cnh }} ({{ $motorista->categoria_cnh }}) @endif
                    </small>
                </div>
                <div class="col-md-6">
                    <div class="row g-2 text-center">
                        <div class="col-3">
                            <div class="text-muted small">Comissão Padrão</div>
                            <div class="fw-bold text-primary">{{ number_format($motorista->percentual_comissao,2,',','.') }}%</div>
                        </div>
                        <div class="col-3">
                            <div class="text-muted small">Telefone</div>
                            <div class="fw-bold">{{ $motorista->telefone ?? '-' }}</div>
                        </div>
                        <div class="col-3">
                            <div class="text-muted small">Validade CNH</div>
                            <div class="fw-bold {{ $motorista->validade_cnh?->isPast() ? 'text-danger' : '' }}">
                                {{ $motorista->validade_cnh?->format('d/m/Y') ?? '-' }}
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="text-muted small">Status</div>
                            <span class="badge {{ $motorista->status === 'ativo' ? 'bg-success' : 'bg-secondary' }}">
                                {{ ucfirst($motorista->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($viagens->count() > 0)

        {{-- ── Cards Totalizadores ── --}}
        <div class="row g-3 mb-4">
            <div class="col-md-2">
                <div class="card text-center border-start border-primary border-3">
                    <div class="card-body py-3">
                        <div class="text-muted small">Viagens</div>
                        <div class="fs-3 fw-bold text-primary">{{ $totais['total_viagens'] }}</div>
                        <div class="text-muted" style="font-size:.7rem">
                            {{ $totais['viagens_encerradas'] }} enc. /
                            {{ $totais['viagens_abertas'] }} abertas
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center border-start border-3" style="border-color:#f97316!important">
                    <div class="card-body py-3">
                        <div class="text-muted small">Total Frete</div>
                        <div class="fw-bold" style="color:#f97316;font-size:.9rem">
                            R$ {{ number_format($totais['total_frete'], 2, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center border-start border-warning border-3">
                    <div class="card-body py-3">
                        <div class="text-muted small">Comissão</div>
                        <div class="fw-bold text-warning" style="font-size:.9rem">
                            R$ {{ number_format($totais['total_comissao'], 2, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center border-start border-danger border-3">
                    <div class="card-body py-3">
                        <div class="text-muted small">Descontos</div>
                        <div class="fw-bold text-danger" style="font-size:.9rem">
                            R$ {{ number_format($totais['total_descontos'], 2, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Saldo a Pagar — viagens abertas --}}
            <div class="col-md-2">
                <div class="card text-center border-start border-warning border-3"
                    style="background:linear-gradient(135deg,#fffbeb,#fef3c7)">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center justify-content-center gap-1 mb-1">
                            <i class="bi bi-clock-history text-warning" style="font-size:.8rem"></i>
                            <span class="text-muted small">Saldo a Pagar</span>
                        </div>
                        <div class="fw-bold text-warning fs-6">
                            R$ {{ number_format($totais['saldo_a_pagar'], 2, ',', '.') }}
                        </div>
                        <div class="text-muted" style="font-size:.7rem">
                            {{ $totais['viagens_abertas'] }}
                            {{ $totais['viagens_abertas'] === 1 ? 'viagem aberta' : 'viagens abertas' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Total Pago — viagens encerradas --}}
            <div class="col-md-2">
                <div class="card text-center border-start border-success border-3"
                    style="background:linear-gradient(135deg,#f0fdf4,#dcfce7)">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center justify-content-center gap-1 mb-1">
                            <i class="bi bi-check-circle-fill text-success" style="font-size:.8rem"></i>
                            <span class="text-muted small">Total Pago</span>
                        </div>
                        <div class="fw-bold text-success fs-6">
                            R$ {{ number_format($totais['saldo_pago'], 2, ',', '.') }}
                        </div>
                        <div class="text-muted" style="font-size:.7rem">
                            {{ $totais['viagens_encerradas'] }}
                            {{ $totais['viagens_encerradas'] === 1 ? 'viagem encerrada' : 'viagens encerradas' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Tabela de Viagens ── --}}
        <div class="card">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between">
                <span><i class="bi bi-list-ul me-2 text-primary"></i>Viagens no Período</span>
                @if($totais['total_km'] > 0)
                <span class="text-muted small">
                    <i class="bi bi-speedometer me-1"></i>
                    Total: {{ number_format($totais['total_km'], 0, ',', '.') }} km rodados
                </span>
                @endif
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Veículo</th>
                            <th>Cliente</th>
                            <th>Rota</th>
                            <th>Saída</th>
                            <th class="text-end">Frete</th>
                            <th class="text-end">Comissão</th>
                            <th class="text-end">Descontos</th>
                            <th class="text-end">Saldo</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($viagens as $viagem)
                        <tr onclick="window.location='{{ route('viagens.show', $viagem) }}'"
                            style="cursor:pointer">
                            <td class="ps-3 text-muted">#{{ $viagem->id }}</td>
                            <td>{{ $viagem->veiculo->placa }}</td>
                            <td>{{ $viagem->cliente->nome ?? '-' }}</td>
                            <td class="small">{{ $viagem->origem }} → {{ $viagem->destino }}</td>
                            <td>{{ $viagem->data_saida->format('d/m/Y') }}</td>
                            <td class="text-end">R$ {{ number_format($viagem->valor_frete, 2, ',', '.') }}</td>
                            <td class="text-end text-warning">R$ {{ number_format($viagem->valor_motorista, 2, ',', '.') }}</td>
                            <td class="text-end text-danger">R$ {{ number_format($viagem->total_descontos, 2, ',', '.') }}</td>
                            <td class="text-end fw-semibold {{ $viagem->saldo_motorista >= 0 ? 'text-success' : 'text-danger' }}">
                                R$ {{ number_format($viagem->saldo_motorista, 2, ',', '.') }}
                            </td>
                            <td>
                                <span class="badge badge-status-{{ $viagem->status }}">
                                    {{ ucfirst(str_replace('_', ' ', $viagem->status)) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="5" class="ps-3">Totais do Período</td>
                            <td class="text-end">R$ {{ number_format($totais['total_frete'], 2, ',', '.') }}</td>
                            <td class="text-end text-warning">R$ {{ number_format($totais['total_comissao'], 2, ',', '.') }}</td>
                            <td class="text-end text-danger">R$ {{ number_format($totais['total_descontos'], 2, ',', '.') }}</td>
                            <td class="text-end {{ $totais['total_saldo'] >= 0 ? 'text-success' : 'text-danger' }}">
                                R$ {{ number_format($totais['total_saldo'], 2, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    @else
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Nenhuma viagem encontrada para <strong>{{ $motorista->nome }}</strong>
            no período de {{ \Carbon\Carbon::parse($dataInicio)->format('d/m/Y') }}
            a {{ \Carbon\Carbon::parse($dataFim)->format('d/m/Y') }}.
        </div>
    @endif

@else
    <div class="card">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-person-check fs-1 d-block mb-3"></i>
            <h5>Selecione um motorista para ver o histórico de acertos</h5>
            <p class="small">Escolha o motorista e o período desejado no filtro acima.</p>
        </div>
    </div>
@endif

@endsection