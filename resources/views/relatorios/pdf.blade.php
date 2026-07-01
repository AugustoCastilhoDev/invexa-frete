<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório Financeiro</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'DejaVu Sans',sans-serif; font-size:10px; color:#1a1a1a; padding:20px; }

        .header { display:flex; justify-content:space-between; align-items:flex-start;
                  border-bottom:3px solid #f97316; padding-bottom:10px; margin-bottom:15px; }
        .brand  { font-size:18px; font-weight:700; color:#1a1a2e; }
        .brand span { color:#f97316; }
        .doc-info { text-align:right; color:#6c757d; font-size:9px; }
        .doc-info h2 { font-size:13px; color:#1a1a2e; font-weight:700; }

        .cards { display:flex; gap:10px; margin-bottom:15px; }
        .card  { flex:1; border:1px solid #e9ecef; border-radius:6px; padding:8px 10px; text-align:center; }
        .card .label { font-size:8px; color:#6c757d; text-transform:uppercase; margin-bottom:3px; }
        .card .value { font-size:12px; font-weight:700; }
        .card.orange .value { color:#f97316; }
        .card.green  .value { color:#10b981; }
        .card.yellow .value { color:#f59e0b; }
        .card.red    .value { color:#ef4444; }
        .card.blue   .value { color:#3b82f6; }

        .section-title { font-size:9px; font-weight:700; text-transform:uppercase;
                         letter-spacing:1px; color:#f97316; border-bottom:1px solid #f0f0f0;
                         padding-bottom:4px; margin-bottom:8px; margin-top:12px; }

        table { width:100%; border-collapse:collapse; margin-bottom:12px; }
        thead tr { background:#f8f9fa; }
        th { padding:5px 6px; font-size:8px; font-weight:700; text-transform:uppercase;
             letter-spacing:.5px; color:#6c757d; text-align:left; border-bottom:1px solid #e9ecef; }
        td { padding:4px 6px; border-bottom:1px solid #f5f5f5; font-size:9px; }
        tfoot td { background:#f8f9fa; font-weight:700; border-top:2px solid #e9ecef; }
        .text-right { text-align:right; }
        .text-center { text-align:center; }
        .text-success { color:#10b981; }
        .text-danger  { color:#ef4444; }
        .text-warning { color:#f59e0b; }

        .badge { display:inline-block; padding:1px 6px; border-radius:10px; font-size:8px; font-weight:700; }
        .badge-encerrada     { background:#d1fae5; color:#065f46; }
        .badge-aberta        { background:#dbeafe; color:#1e40af; }
        .badge-em_andamento  { background:#fef3c7; color:#92400e; }
        .badge-aguardando_acerto { background:#ede9fe; color:#5b21b6; }

        .footer { margin-top:15px; padding-top:8px; border-top:1px solid #e9ecef;
                  text-align:center; font-size:8px; color:#adb5bd; }
    </style>
</head>
<body>

    <div class="header">
        <div>
            <div class="brand">Invexa <span>Frete</span></div>
            <div style="font-size:9px;color:#6c757d">Sistema de Gestão de Viagens</div>
        </div>
        <div class="doc-info">
            <h2>Relatório Financeiro</h2>
            <div>Período: {{ \Carbon\Carbon::parse($dataInicio)->format('d/m/Y') }}
                 a {{ \Carbon\Carbon::parse($dataFim)->format('d/m/Y') }}</div>
            <div>Status: {{ ucfirst(str_replace('_', ' ', $statusSel)) }}</div>
            <div>Emitido em {{ now()->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    {{-- Cards --}}
    <div class="cards">
        <div class="card blue">
            <div class="label">Viagens</div>
            <div class="value">{{ $totais['total_viagens'] }}</div>
        </div>
        <div class="card orange">
            <div class="label">Faturamento</div>
            <div class="value">R$ {{ number_format($totais['frete'], 2, ',', '.') }}</div>
        </div>
        <div class="card yellow">
            <div class="label">Comissões</div>
            <div class="value">R$ {{ number_format($totais['motoristas'], 2, ',', '.') }}</div>
        </div>
        <div class="card red">
            <div class="label">Combustível</div>
            <div class="value">R$ {{ number_format($totais['combustivel'], 2, ',', '.') }}</div>
        </div>
        <div class="card red">
            <div class="label">Manutenção</div>
            <div class="value">R$ {{ number_format($totais['manutencao'], 2, ',', '.') }}</div>
        </div>
        <div class="card green">
            <div class="label">Lucro Líquido</div>
            <div class="value">R$ {{ number_format($totais['lucro'], 2, ',', '.') }}</div>
        </div>
    </div>

    {{-- Por Motorista --}}
    <div class="section-title">Resumo por Motorista</div>
    <table>
        <thead>
            <tr>
                <th>Motorista</th>
                <th class="text-center">Viagens</th>
                <th class="text-right">Frete Total</th>
                <th class="text-right">Comissão</th>
                <th class="text-right">Saldo a Pagar</th>
            </tr>
        </thead>
        <tbody>
            @foreach($porMotorista as $item)
            <tr>
                <td>{{ $item['nome'] }}</td>
                <td class="text-center">{{ $item['viagens'] }}</td>
                <td class="text-right">R$ {{ number_format($item['frete'], 2, ',', '.') }}</td>
                <td class="text-right">R$ {{ number_format($item['comissao'], 2, ',', '.') }}</td>
                <td class="text-right {{ $item['saldo'] >= 0 ? 'text-success' : 'text-danger' }}">
                    R$ {{ number_format($item['saldo'], 2, ',', '.') }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Detalhamento --}}
    <div class="section-title">Detalhamento das Viagens</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Motorista</th>
                <th>Veículo</th>
                <th>Rota</th>
                <th>Saída</th>
                <th class="text-right">Frete</th>
                <th class="text-right">Combustível</th>
                <th class="text-right">Manutenção</th>
                <th class="text-right">Comissão</th>
                <th class="text-right">Lucro</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($viagens as $viagem)
            <tr>
                <td>#{{ $viagem->id }}</td>
                <td>{{ $viagem->motorista->nome }}</td>
                <td>{{ $viagem->veiculo->placa }}</td>
                <td>{{ $viagem->origem }} → {{ $viagem->destino }}</td>
                <td>{{ $viagem->data_saida->format('d/m/Y') }}</td>
                <td class="text-right">R$ {{ number_format($viagem->valor_frete, 2, ',', '.') }}</td>
                <td class="text-right text-warning">R$ {{ number_format($viagem->total_combustivel, 2, ',', '.') }}</td>
                <td class="text-right text-danger">R$ {{ number_format($viagem->total_manutencao, 2, ',', '.') }}</td>
                <td class="text-right">R$ {{ number_format($viagem->valor_motorista, 2, ',', '.') }}</td>
                <td class="text-right {{ $viagem->lucro_transportadora >= 0 ? 'text-success' : 'text-danger' }}">
                    R$ {{ number_format($viagem->lucro_transportadora, 2, ',', '.') }}
                </td>
                <td><span class="badge badge-{{ $viagem->status }}">
                    {{ ucfirst(str_replace('_',' ',$viagem->status)) }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5">Totais</td>
                <td class="text-right">R$ {{ number_format($totais['frete'], 2, ',', '.') }}</td>
                <td class="text-right">R$ {{ number_format($totais['combustivel'], 2, ',', '.') }}</td>
                <td class="text-right">R$ {{ number_format($totais['manutencao'], 2, ',', '.') }}</td>
                <td class="text-right">R$ {{ number_format($totais['motoristas'], 2, ',', '.') }}</td>
                <td class="text-right {{ $totais['lucro'] >= 0 ? 'text-success' : 'text-danger' }}">
                    R$ {{ number_format($totais['lucro'], 2, ',', '.') }}
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Invexa Frete — Relatório gerado em {{ now()->format('d/m/Y \à\s H:i') }}
    </div>

</body>
</html>