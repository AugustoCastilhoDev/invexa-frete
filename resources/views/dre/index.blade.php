@extends('layouts.app')
@section('title', 'DRE')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Demonstrativo de Resultado (DRE)</h4>
        <small class="text-muted">Resultado do período, considerando apenas viagens encerradas</small>
    </div>
    <a href="{{ route('dre.pdf', request()->query()) }}" target="_blank" class="btn btn-outline-dark">
        <i class="bi bi-printer me-1"></i> Exportar PDF
    </a>
</div>

{{-- Filtros --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('dre.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Data Início</label>
                    <input type="date" name="data_inicio" class="form-control form-control-sm" value="{{ $dataInicio }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Data Fim</label>
                    <input type="date" name="data_fim" class="form-control form-control-sm" value="{{ $dataFim }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body p-4" style="background:#16213e;color:#fff;border-radius:12px">

                <div class="d-flex justify-content-between py-2">
                    <span class="fw-semibold">RECEITA BRUTA</span>
                    <span></span>
                </div>
                <div class="d-flex justify-content-between py-1 ps-3 border-bottom border-secondary-subtle">
                    <span>Faturamento (viagens encerradas)</span>
                    <span>R$ {{ number_format($receitaBruta, 2, ',', '.') }}</span>
                </div>

                <div class="d-flex justify-content-between py-2 mt-2">
                    <span class="fw-semibold">(-) CUSTOS DIRETOS</span>
                    <span></span>
                </div>
                <div class="d-flex justify-content-between py-1 ps-3">
                    <span class="text-warning">Comissão de motoristas</span>
                    <span class="text-warning">R$ {{ number_format($comissaoMotoristas, 2, ',', '.') }}</span>
                </div>
                <div class="d-flex justify-content-between py-1 ps-3">
                    <span class="text-warning">Combustível</span>
                    <span class="text-warning">R$ {{ number_format($combustivel, 2, ',', '.') }}</span>
                </div>
                <div class="d-flex justify-content-between py-1 ps-3 border-bottom border-secondary-subtle">
                    <span class="text-warning">Manutenção (durante viagem)</span>
                    <span class="text-warning">R$ {{ number_format($manutencaoViagem, 2, ',', '.') }}</span>
                </div>

                <div class="d-flex justify-content-between py-2 mt-2 fw-bold fs-5" style="color:#22c55e">
                    <span>= RESULTADO BRUTO</span>
                    <span>R$ {{ number_format($resultadoBruto, 2, ',', '.') }}</span>
                </div>

                <div class="d-flex justify-content-between py-2 mt-3">
                    <span class="fw-semibold">(-) DESPESAS OPERACIONAIS</span>
                    <span></span>
                </div>
                <div class="d-flex justify-content-between py-1 ps-3">
                    <span class="text-warning">Manutenção de frota (fora de viagem)</span>
                    <span class="text-warning">R$ {{ number_format($manutencaoFrota, 2, ',', '.') }}</span>
                </div>
                <div class="d-flex justify-content-between py-1 ps-3 border-bottom border-secondary-subtle">
                    <span class="text-warning">Despesas administrativas</span>
                    <span class="text-warning">R$ {{ number_format($despesasGerais, 2, ',', '.') }}</span>
                </div>

                <div class="d-flex justify-content-between py-3 mt-2 fw-bold fs-4"
                     style="border-top:2px solid rgba(255,255,255,.2); color: {{ $resultadoLiquido >= 0 ? '#22c55e' : '#ef4444' }}">
                    <span>= RESULTADO LÍQUIDO</span>
                    <span>R$ {{ number_format($resultadoLiquido, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card mb-4">
            <div class="card-header bg-white">
                <strong><i class="bi bi-pie-chart me-1"></i> Despesas administrativas por categoria</strong>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <tbody>
                        @forelse($despesasPorCategoria as $item)
                        <tr>
                            <td class="ps-3">{{ $item['rotulo'] }}</td>
                            <td class="text-end pe-3">R$ {{ number_format($item['total'], 2, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td class="text-center text-muted py-3">Nenhuma despesa administrativa no período.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        <div class="alert alert-secondary small mb-0">
            <i class="bi bi-info-circle me-1"></i>
            A receita considera apenas viagens com status <strong>Encerrada</strong> dentro do período selecionado
            (filtrado pela data de saída). Cadastre despesas administrativas em
            <a href="{{ route('despesas-gerais.index') }}">Despesas Gerais</a>.
        </div>
    </div>
</div>
@endsection
