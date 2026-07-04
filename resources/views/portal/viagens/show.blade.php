@extends('layouts.portal')
@section('title', 'Viagem #' . $viagem->id)

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h4 class="mb-0">Viagem #{{ $viagem->id }}
            <span class="badge badge-status-{{ $viagem->status }} ms-2" style="font-size:.7rem">
                {{ ucfirst(str_replace('_',' ',$viagem->status)) }}
            </span>
        </h4>
        <small class="text-muted">
            {{ $viagem->veiculo->placa }} — {{ $viagem->origem }} → {{ $viagem->destino }}
        </small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('portal.viagens.comprovante', $viagem) }}" target="_blank" class="btn btn-outline-dark btn-sm">
            <i class="bi bi-printer me-1"></i> Baixar Comprovante
        </a>
        <a href="{{ route('portal.viagens.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Voltar
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card text-center border-start border-primary border-3">
            <div class="card-body py-3">
                <div class="text-muted small mb-1">Valor do Frete</div>
                <div class="fw-bold fs-5 text-primary">R$ {{ number_format($viagem->valor_frete, 2, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center border-start border-warning border-3">
            <div class="card-body py-3">
                <div class="text-muted small mb-1">Sua Comissão ({{ number_format($viagem->percentual_motorista, 2, ',', '.') }}%)</div>
                <div class="fw-bold fs-5 text-warning">R$ {{ number_format($viagem->valor_motorista, 2, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center border-start border-success border-3">
            <div class="card-body py-3">
                <div class="text-muted small mb-1">Saldo</div>
                <div class="fw-bold fs-5 {{ $viagem->saldo_motorista >= 0 ? 'text-success' : 'text-danger' }}">
                    R$ {{ number_format($viagem->saldo_motorista, 2, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-info-circle me-2"></i>Dados da Viagem
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted">Cliente</td><td>{{ $viagem->cliente->nome ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Saída</td><td>{{ $viagem->data_saida->format('d/m/Y') }}</td></tr>
                    <tr><td class="text-muted">Retorno</td><td>{{ $viagem->data_retorno?->format('d/m/Y') ?? 'Em aberto' }}</td></tr>
                    <tr><td class="text-muted">KM Rodados</td><td>{{ $viagem->km_rodados > 0 ? number_format($viagem->km_rodados, 0, ',', '.') . ' km' : '-' }}</td></tr>
                    <tr><td class="text-muted">Adiantamento</td><td>R$ {{ number_format($viagem->valor_adiantamento, 2, ',', '.') }}</td></tr>
                </table>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-file-earmark-text me-2"></i>Documentos Fiscais
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th class="ps-3">Tipo</th><th>Número</th><th class="text-end pe-3">Arquivo</th></tr>
                    </thead>
                    <tbody>
                        @forelse($viagem->documentos as $doc)
                        <tr>
                            <td class="ps-3">{{ $doc->tipo_formatado }}</td>
                            <td>{{ $doc->numero }}</td>
                            <td class="text-end pe-3">
                                @if($doc->arquivo)
                                <a href="{{ $doc->arquivo_url }}" target="_blank"><i class="bi bi-download"></i></a>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">Nenhum documento.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-receipt me-2"></i>Lançamentos
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th class="ps-3">Tipo</th><th>Descrição</th><th>Valor</th><th class="pe-3">Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse($viagem->lancamentos as $l)
                        <tr>
                            <td class="ps-3">{{ ucfirst($l->tipo) }}</td>
                            <td>{{ $l->descricao }}</td>
                            <td>R$ {{ number_format($l->valor, 2, ',', '.') }}</td>
                            <td class="pe-3">
                                <span class="badge bg-{{ $l->status_badge }}">{{ ucfirst($l->status) }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">Nenhum lançamento.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($viagem->status !== 'encerrada')
            <div class="card-footer bg-white">
                <form action="{{ route('portal.lancamentos.store', $viagem) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-2">
                        <div class="col-sm-4">
                            <select name="tipo" class="form-select form-select-sm" required>
                                <option value="">Tipo</option>
                                <option value="combustivel">Combustível</option>
                                <option value="manutencao">Manutenção</option>
                                <option value="outros">Outros</option>
                            </select>
                        </div>
                        <div class="col-sm-5">
                            <input type="text" name="descricao" class="form-control form-control-sm" placeholder="Descrição" required>
                        </div>
                        <div class="col-sm-3">
                            <input type="number" name="valor" class="form-control form-control-sm" placeholder="Valor" step="0.01" min="0" required>
                        </div>
                        <input type="hidden" name="data_lancamento" value="{{ date('Y-m-d') }}">
                        <div class="col-9">
                            <input type="file" name="comprovante" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.pdf" required>
                        </div>
                        <div class="col-3">
                            <button type="submit" class="btn btn-warning btn-sm w-100">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-text">
                        Anexe a foto do comprovante (combustível, manutenção etc). Fica pendente até a transportadora aprovar.
                    </div>
                </form>
            </div>
            @endif
        </div>

        <div class="card mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-dash-circle me-2"></i>Descontos
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th class="ps-3">Tipo</th><th>Descrição</th><th class="text-end pe-3">Valor</th></tr>
                    </thead>
                    <tbody>
                        @forelse($viagem->descontos as $d)
                        <tr>
                            <td class="ps-3">{{ ucfirst($d->tipo) }}</td>
                            <td>{{ $d->descricao }}</td>
                            <td class="text-end pe-3">R$ {{ number_format($d->valor, 2, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">Nenhum desconto.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($viagem->assinatura_motorista_path)
        <div class="card mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-pen me-2"></i>Sua Assinatura
            </div>
            <div class="card-body">
                <img src="{{ $viagem->assinatura_motorista_url }}" alt="Assinatura" style="max-width:100%;max-height:100px">
                <div class="text-muted small mt-2">
                    Assinado em {{ $viagem->assinatura_motorista_em->format('d/m/Y \à\s H:i') }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
