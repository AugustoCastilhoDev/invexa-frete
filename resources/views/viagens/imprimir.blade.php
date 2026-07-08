<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acerto de Viagem #{{ $viagem->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            background: #fff;
            padding: 30px;
        }

        /* ── Cabeçalho ── */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #f97316;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }
        .brand-name {
            font-size: 22px;
            font-weight: 700;
            color: #1a1a2e;
        }
        .brand-name span { color: #f97316; }
        .brand-sub {
            font-size: 10px;
            color: #6c757d;
            margin-top: 2px;
        }
        .doc-title {
            text-align: right;
        }
        .doc-title h2 {
            font-size: 15px;
            font-weight: 700;
            color: #1a1a2e;
        }
        .doc-title .badge-status {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-top: 4px;
        }
        .status-encerrada     { background:#d1fae5; color:#065f46; }
        .status-aberta        { background:#dbeafe; color:#1e40af; }
        .status-em_andamento  { background:#fef3c7; color:#92400e; }
        .status-aguardando_acerto { background:#ede9fe; color:#5b21b6; }

        /* ── Seções ── */
        .section {
            margin-bottom: 16px;
        }
        .section-title {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #f97316;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }

        /* ── Grid de dados ── */
        .data-grid {
            width: 100%;
            border-collapse: collapse;
        }
        .data-grid td {
            padding: 4px 6px;
            vertical-align: top;
        }
        .data-grid .label {
            color: #6c757d;
            width: 35%;
            font-size: 10px;
        }
        .data-grid .value {
            font-weight: 600;
            color: #1a1a1a;
        }

        /* ── Tabelas de lançamentos ── */
        .table-list {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        .table-list thead tr {
            background: #f8f9fa;
        }
        .table-list th {
            padding: 5px 8px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #6c757d;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        .table-list td {
            padding: 5px 8px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 10px;
        }
        .table-list .text-right { text-align: right; }
        .table-list tfoot td {
            padding: 6px 8px;
            font-weight: 700;
            font-size: 10px;
            border-top: 2px solid #e9ecef;
            background: #f8f9fa;
        }
        .empty-row td {
            color: #adb5bd;
            font-style: italic;
            text-align: center;
            padding: 8px;
        }

        /* ── Resumo financeiro ── */
        .resumo-box {
            background: #1a1a2e;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 16px;
        }
        .resumo-box .resumo-title {
            color: #f97316;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        .resumo-table {
            width: 100%;
            border-collapse: collapse;
        }
        .resumo-table td {
            padding: 4px 0;
            font-size: 10px;
            color: rgba(255,255,255,.8);
        }
        .resumo-table .resumo-value {
            text-align: right;
            font-weight: 600;
            color: #fff;
        }
        .resumo-table .resumo-deduct {
            text-align: right;
            font-weight: 600;
            color: #fbbf24;
        }
        .resumo-table .resumo-total {
            font-size: 12px;
            font-weight: 700;
            color: #fff;
        }
        .resumo-table .resumo-total-value {
            text-align: right;
            font-size: 12px;
            font-weight: 700;
        }
        .resumo-table .divider td {
            border-top: 1px solid rgba(255,255,255,.15);
            padding-top: 8px;
            margin-top: 4px;
        }
        .text-success-light { color: #6ee7b7; }
        .text-danger-light  { color: #fca5a5; }

        /* ── Assinaturas ── */
        .assinaturas {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        .assinatura-box {
            width: 45%;
            text-align: center;
        }
        .assinatura-linha {
            border-top: 1px solid #1a1a1a;
            margin-bottom: 6px;
        }
        .assinatura-label {
            font-size: 10px;
            color: #6c757d;
        }
        .assinatura-nome {
            font-size: 11px;
            font-weight: 700;
            color: #1a1a1a;
        }

        /* ── Rodapé ── */
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e9ecef;
            text-align: center;
            font-size: 9px;
            color: #adb5bd;
        }

        /* ── Layout de duas colunas ──
           Usa <table> em vez de flexbox: o Dompdf (gerador do PDF) não
           renderiza display:flex de forma confiável, o que fazia as colunas
           ficarem empilhadas em vez de lado a lado. */
        .two-col {
            width: 100%;
            border-collapse: collapse;
        }
        .two-col > tbody > tr > td.col-half {
            width: 50%;
            vertical-align: top;
            padding: 0;
        }
        .two-col > tbody > tr > td.col-half:first-child { padding-right: 8px; }
        .two-col > tbody > tr > td.col-half:last-child { padding-left: 8px; }

        /* ── Chip de tipo ── */
        .chip {
            display: inline-block;
            padding: 1px 7px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: 700;
        }
        .chip-combustivel { background:#fef3c7; color:#92400e; }
        .chip-manutencao  { background:#fee2e2; color:#991b1b; }
        .chip-outros      { background:#f0f0f0; color:#6c757d; }
        .chip-vale        { background:#fee2e2; color:#991b1b; }
        .chip-multa       { background:#fee2e2; color:#7f1d1d; }
        .chip-adiantamento{ background:#ede9fe; color:#5b21b6; }
        .chip-bonificacao { background:#d1fae5; color:#065f46; }
    </style>
</head>
<body>

    {{-- ── CABEÇALHO ── --}}
    <div class="header">
        <div>
            <div class="brand-name">Invexa <span>Frete</span></div>
            <div class="brand-sub">Sistema de Gestão de Viagens</div>
        </div>
        <div class="doc-title">
            <h2>Acerto de Viagem #{{ $viagem->id }}</h2>
            <div>
                <span class="badge-status status-{{ $viagem->status }}">
                    {{ ucfirst(str_replace('_', ' ', $viagem->status)) }}
                </span>
            </div>
            <div style="color:#6c757d;font-size:9px;margin-top:4px">
                Emitido em {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>

    {{-- ── MOTORISTA E VEÍCULO ── --}}
    <table class="two-col section">
        <tr>
            <td class="col-half">
                <div class="section-title">Motorista</div>
                <table class="data-grid">
                    <tr>
                        <td class="label">Nome</td>
                        <td class="value">{{ $viagem->motorista->nome }}</td>
                    </tr>
                    <tr>
                        <td class="label">CPF</td>
                        <td class="value">{{ $viagem->motorista->cpf }}</td>
                    </tr>
                    <tr>
                        <td class="label">CNH</td>
                        <td class="value">{{ $viagem->motorista->cnh ?? '-' }}
                            {{ $viagem->motorista->categoria_cnh ? '('.$viagem->motorista->categoria_cnh.')' : '' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Comissão</td>
                        <td class="value">{{ number_format($viagem->percentual_motorista, 2, ',', '.') }}%</td>
                    </tr>
                </table>
            </td>
            <td class="col-half">
                <div class="section-title">Veículo</div>
                <table class="data-grid">
                    <tr>
                        <td class="label">Placa</td>
                        <td class="value">{{ $viagem->veiculo->placa }}</td>
                    </tr>
                    <tr>
                        <td class="label">Modelo</td>
                        <td class="value">{{ $viagem->veiculo->modelo }}
                            {{ $viagem->veiculo->marca ? '/ '.$viagem->veiculo->marca : '' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Tipo</td>
                        <td class="value">{{ ucfirst($viagem->veiculo->tipo) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Ano</td>
                        <td class="value">{{ $viagem->veiculo->ano ?? '-' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ── DADOS DA VIAGEM ── --}}
    <div class="section">
        <div class="section-title">Dados da Viagem</div>
        <table class="data-grid">
            <tr>
                <td class="label">Origem</td>
                <td class="value">{{ $viagem->origem }}</td>
                <td class="label">Destino</td>
                <td class="value">{{ $viagem->destino }}</td>
            </tr>
            <tr>
                <td class="label">Cliente</td>
                <td class="value">{{ $viagem->cliente->nome ?? '-' }}</td>
                <td class="label">Valor do Frete</td>
                <td class="value">R$ {{ number_format($viagem->valor_frete, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Saída</td>
                <td class="value">{{ $viagem->data_saida->format('d/m/Y') }}</td>
                <td class="label">Retorno</td>
                <td class="value">{{ $viagem->data_retorno?->format('d/m/Y') ?? 'Em aberto' }}</td>
            </tr>
            <tr>
                <td class="label">KM Inicial</td>
                <td class="value">{{ $viagem->km_inicial ? number_format($viagem->km_inicial, 0, ',', '.') : '-' }}</td>
                <td class="label">KM Final</td>
                <td class="value">{{ $viagem->km_final ? number_format($viagem->km_final, 0, ',', '.') : '-' }}</td>
            </tr>
            @if($viagem->km_rodados > 0)
            <tr>
                <td class="label">KM Rodados</td>
                <td class="value" colspan="3">
                    {{ number_format($viagem->km_rodados, 0, ',', '.') }} km
                    @if($viagem->media_combustivel !== null)
                        — Média: {{ number_format($viagem->media_combustivel, 2, ',', '.') }} km/L ({{ number_format($viagem->total_litros, 2, ',', '.') }} L)
                    @endif
                </td>
            </tr>
            @endif
            @if($viagem->observacoes)
            <tr>
                <td class="label">Observações</td>
                <td class="value" colspan="3">{{ $viagem->observacoes }}</td>
            </tr>
            @endif
        </table>
    </div>

    {{-- ── LANÇAMENTOS ── --}}
    <table class="two-col section">
        <tr>
            <td class="col-half">
                <div class="section-title">Lançamentos de Despesas</div>
                <table class="table-list">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Descrição</th>
                            <th>Data</th>
                            <th>KM</th>
                            <th>Litros</th>
                            <th class="text-right">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($viagem->lancamentos as $l)
                        <tr>
                            <td><span class="chip chip-{{ $l->tipo }}">{{ ucfirst($l->tipo) }}</span></td>
                            <td>{{ $l->descricao }}</td>
                            <td>{{ $l->data_lancamento->format('d/m') }}</td>
                            <td>{{ $l->km_veiculo ? number_format($l->km_veiculo, 0, ',', '.') : '-' }}</td>
                            <td>{{ $l->litros ? number_format($l->litros, 2, ',', '.') : '-' }}</td>
                            <td class="text-right">R$ {{ number_format($l->valor, 2, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr class="empty-row"><td colspan="6">Sem lançamentos</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5">Total Despesas</td>
                            <td class="text-right">
                                R$ {{ number_format($viagem->total_combustivel + $viagem->total_manutencao, 2, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </td>

            <td class="col-half">
                <div class="section-title">Descontos e Bonificações do Motorista</div>
                <table class="table-list">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Descrição</th>
                            <th>Data</th>
                            <th class="text-right">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($viagem->descontos as $d)
                        <tr>
                            <td><span class="chip chip-{{ $d->tipo }}">{{ ucfirst($d->tipo) }}</span></td>
                            <td>{{ $d->descricao }}</td>
                            <td>{{ $d->data_desconto->format('d/m') }}</td>
                            <td class="text-right" style="{{ $d->tipo === 'bonificacao' ? 'color:#065f46;font-weight:700' : '' }}">
                                {{ $d->tipo === 'bonificacao' ? '+' : '' }} R$ {{ number_format($d->valor, 2, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr class="empty-row"><td colspan="4">Sem descontos</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3">Total Descontos</td>
                            <td class="text-right">
                                R$ {{ number_format($viagem->total_descontos, 2, ',', '.') }}
                            </td>
                        </tr>
                        @if($viagem->total_bonificacoes > 0)
                        <tr>
                            <td colspan="3" style="color:#065f46">Total Bonificações</td>
                            <td class="text-right" style="color:#065f46">
                                + R$ {{ number_format($viagem->total_bonificacoes, 2, ',', '.') }}
                            </td>
                        </tr>
                        @endif
                    </tfoot>
                </table>
            </td>
        </tr>
    </table>

    {{-- ── RESUMO FINANCEIRO ── --}}
    <div class="resumo-box">
        <div class="resumo-title">Resumo Financeiro da Viagem</div>
        <table class="resumo-table">
            <tr>
                <td>Valor do Frete</td>
                <td class="resumo-value">R$ {{ number_format($viagem->valor_frete, 2, ',', '.') }}</td>
                <td width="40"></td>
                <td>Comissão Motorista ({{ number_format($viagem->percentual_motorista,2,',','.') }}%)</td>
                <td class="resumo-value">R$ {{ number_format($viagem->valor_motorista, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>(-) Comissão Motorista</td>
                <td class="resumo-deduct">R$ {{ number_format($viagem->valor_motorista, 2, ',', '.') }}</td>
                <td></td>
                <td>(-) Total Descontos</td>
                <td class="resumo-deduct">R$ {{ number_format($viagem->total_descontos, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>(-) Total Combustível</td>
                <td class="resumo-deduct">R$ {{ number_format($viagem->total_combustivel, 2, ',', '.') }}</td>
                <td></td>
                <td>(-) Adiantamento</td>
                <td class="resumo-deduct">R$ {{ number_format($viagem->valor_adiantamento, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>(-) Total Manutenção</td>
                <td class="resumo-deduct">R$ {{ number_format($viagem->total_manutencao, 2, ',', '.') }}</td>
                <td></td>
                <td>(+) Bonificações</td>
                <td class="text-success-light" style="text-align:right;font-weight:600">
                    R$ {{ number_format($viagem->total_bonificacoes, 2, ',', '.') }}
                </td>
            </tr>
            <tr class="divider">
                <td class="resumo-total">Lucro da Transportadora</td>
                <td class="resumo-total-value {{ $viagem->lucro_transportadora >= 0 ? 'text-success-light' : 'text-danger-light' }}">
                    R$ {{ number_format($viagem->lucro_transportadora, 2, ',', '.') }}
                </td>
                <td></td>
                <td class="resumo-total">Saldo a Pagar ao Motorista</td>
                <td class="resumo-total-value {{ $viagem->saldo_motorista >= 0 ? 'text-success-light' : 'text-danger-light' }}">
                    R$ {{ number_format($viagem->saldo_motorista, 2, ',', '.') }}
                </td>
            </tr>
        </table>
    </div>

    {{-- ── ASSINATURAS ── --}}
    <div class="assinaturas">
        <div class="assinatura-box">
            @if($assinaturaBase64)
                <img src="{{ $assinaturaBase64 }}" alt="Assinatura" style="max-height:50px;max-width:100%">
            @else
                <div class="assinatura-linha"></div>
            @endif
            <div class="assinatura-nome">{{ $viagem->motorista->nome }}</div>
            <div class="assinatura-label">
                Motorista — CPF: {{ $viagem->motorista->cpf }}
                @if($assinaturaBase64)
                    <br>Assinado digitalmente em {{ $viagem->assinatura_motorista_em->format('d/m/Y \à\s H:i') }}
                @endif
            </div>
        </div>
        <div class="assinatura-box">
            <div class="assinatura-linha"></div>
            <div class="assinatura-nome">Responsável pela Transportadora</div>
            <div class="assinatura-label">Assinatura e Carimbo</div>
        </div>
    </div>

    {{-- ── RODAPÉ ── --}}
    <div class="footer">
        Documento gerado pelo Invexa Frete — Sistema de Gestão de Viagens •
        Viagem #{{ $viagem->id }} •
        {{ now()->format('d/m/Y \à\s H:i') }}
    </div>

</body>
</html>