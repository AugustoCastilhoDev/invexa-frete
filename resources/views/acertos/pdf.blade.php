<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Acerto de Motorista</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'DejaVu Sans',sans-serif; font-size:10px; color:#1a1a1a; padding:25px; }

        .header { display:flex; justify-content:space-between; align-items:flex-start;
                  border-bottom:3px solid #f97316; padding-bottom:12px; margin-bottom:16px; }
        .brand { font-size:20px; font-weight:700; color:#1a1a2e; }
        .brand span { color:#f97316; }
        .doc-info { text-align:right; color:#6c757d; font-size:9px; }
        .doc-info h2 { font-size:14px; color:#1a1a2e; font-weight:700; }

        .motorista-box { background:#f8f9fa; border-radius:8px; padding:12px 16px;
                         margin-bottom:16px; border-left:4px solid #f97316; }
        .motorista-box h3 { font-size:14px; font-weight:700; color:#1a1a2e; margin-bottom:4px; }
        .motorista-meta { display:flex; gap:20px; font-size:9px; color:#6c757d; }

        .cards { display:flex; gap:8px; margin-bottom:16px; }
        .card  { flex:1; border:1px solid #e9ecef; border-radius:6px;
                 padding:8px; text-align:center; }
        .card .label { font-size:8px; color:#6c757d; text-transform:uppercase; margin-bottom:3px; }
        .card .value { font-size:11px; font-weight:700; }
        .orange { color:#f97316; }
        .green  { color:#10b981; }
        .yellow { color:#f59e0b; }
        .red    { color:#ef4444; }
        .blue   { color:#3b82f6; }

        .section-title { font-size:9px; font-weight:700; text-transform:uppercase;
                         letter-spacing:1px; color:#f97316; border-bottom:1px solid #f0f0f0;
                         padding-bottom:4px; margin-bottom:8px; margin-top:14px; }

        table { width:100%; border-collapse:collapse; }
        thead tr { background:#f8f9fa; }
        th { padding:5px 6px; font-size:8px; font-weight:700; text-transform:uppercase;
             letter-spacing:.5px; color:#6c757d; text-align:left;
             border-bottom:1px solid #e9ecef; }
        td { padding:4px 6px; border-bottom:1px solid #f5f5f5; font-size:9px; }
        tfoot td { background:#f8f9fa; font-weight:700; font-size:9px;
                   border-top:2px solid #e9ecef; }
        .text-right  { text-align:right; }
        .text-center { text-align:center; }
        .text-success { color:#10b981; }
        .text-danger  { color:#ef4444; }
        .text-warning { color:#f59e0b; }
        .text-muted   { color:#6c757d; }

        .badge { display:inline-block; padding:1px 6px; border-radius:10px;
                 font-size:8px; font-weight:700; }
        .badge-encerrada     { background:#d1fae5; color:#065f46; }
        .badge-aberta        { background:#dbeafe; color:#1e40af; }
        .badge-em_andamento  { background:#fef3c7; color:#92400e; }
        .badge-aguardando_acerto { background:#ede9fe; color:#5b21b6; }

        .assinaturas { display:flex; justify-content:space-between; margin-top:30px; }
        .assinatura-box { width:45%; text-align:center; }
        .assinatura-linha { border-top:1px solid #1a1a1a; margin-bottom:6px; }
        .assinatura-label { font-size:9px; color:#6c757d; }
        .assinatura-nome  { font-size:10px; font-weight:700; }

        .footer { margin-top:16px; padding-top:8px; border-top:1px solid #e9ecef;
                  text-align:center; font-size:8px; color:#adb5bd; }
    </style>
</head>
<body>

    {{-- Cabeçalho --}}
    <div class="header">
        <div>
            <div class="brand">Invexa <span>Frete</span></div>
            <div style="font-size:9px;color:#6c757d">Sistema de Gestão de Viagens</div>
        </div>
        <div class="doc-info">
            <h2>Histórico de Acertos</h2>
            <div>Período: {{ \Carbon\Carbon::parse($dataInicio)->format('d/m/Y') }}
                 a {{ \Carbon\Carbon::parse($dataFim)->format('d/m/Y') }}</div>
            <div>Emitido em: {{ now()->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    {{-- Dados do Motorista --}}
    <div class="motorista-box">
        <h3>{{ $motorista->nome }}</h3>
        <div class="motorista-meta">
            <span>CPF: {{ $motorista->cpf }}</span>
            @if($motorista->cnh)
            <span>CNH: {{ $motorista->cnh }} ({{ $motorista->categoria_cnh }})</span>
            @endif
            @if($motorista->validade_cnh)
            <span>Validade CNH: {{ $motorista->validade_cnh->format('d/m/Y') }}</span>
            @endif
            <span>Comissão Padrão: {{ number_format($motorista->percentual_comissao,2,',','.')  }}%</span>
            @if($motorista->telefone)
            <span>Tel: {{ $motorista->telefone }}</span>
            @endif
        </div>
    </div>

    {{-- Cards --}}
    <div class="cards">
    <div class="card">
        <div class="label">Viagens</div>
        <div class="value blue">{{ $totais['total_viagens'] }}</div>
    </div>
    <div class="card">
        <div class="label">Total Frete</div>
        <div class="value orange">R$ {{ number_format($totais['total_frete'], 2, ',', '.') }}</div>
    </div>
    <div class="card">
        <div class="label">Comissão</div>
        <div class="value yellow">R$ {{ number_format($totais['total_comissao'], 2, ',', '.') }}</div>
    </div>
    <div class="card">
        <div class="label">Descontos</div>
        <div class="value red">R$ {{ number_format($totais['total_descontos'], 2, ',', '.') }}</div>
    </div>
    <div class="card">
        <div class="label">⏳ Saldo a Pagar</div>
        <div class="value yellow">R$ {{ number_format($totais['saldo_a_pagar'], 2, ',', '.') }}</div>
        <div style="font-size:7px;color:#6c757d">{{ $totais['viagens_abertas'] }} viagem(ns) abertas</div>
    </div>
    <div class="card">
        <div class="label">✅ Total Pago</div>
        <div class="value green">R$ {{ number_format($totais['saldo_pago'], 2, ',', '.') }}</div>
        <div style="font-size:7px;color:#6c757d">{{ $totais['viagens_encerradas'] }} viagem(ns) encerradas</div>
    </div>
</div>

    {{-- Tabela de Viagens --}}
    <div class="section-title">Detalhamento das Viagens</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Veículo</th>
                <th>Cliente</th>
                <th>Rota</th>
                <th>Saída</th>
                <th class="text-right">Frete</th>
                <th class="text-right">Comissão</th>
                <th class="text-right">Descontos</th>
                <th class="text-right">Adiant.</th>
                <th class="text-right">Saldo</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($viagens as $viagem)
            <tr>
                <td class="text-muted">#{{ $viagem->id }}</td>
                <td>{{ $viagem->veiculo->placa }}</td>
                <td>{{ $viagem->cliente->nome ?? '-' }}</td>
                <td>{{ $viagem->origem }} → {{ $viagem->destino }}</td>
                <td>{{ $viagem->data_saida->format('d/m/Y') }}</td>
                <td class="text-right">R$ {{ number_format($viagem->valor_frete, 2, ',', '.') }}</td>
                <td class="text-right text-warning">R$ {{ number_format($viagem->valor_motorista, 2, ',', '.') }}</td>
                <td class="text-right text-danger">R$ {{ number_format($viagem->total_descontos, 2, ',', '.') }}</td>
                <td class="text-right text-danger">
                    R$ {{ number_format($viagem->adiantamento_descontavel ? $viagem->valor_adiantamento : 0, 2, ',', '.') }}
                </td>
                <td class="text-right {{ $viagem->saldo_motorista >= 0 ? 'text-success' : 'text-danger' }}">
                    R$ {{ number_format($viagem->saldo_motorista, 2, ',', '.') }}
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
                <td class="text-right">R$ {{ number_format($totais['total_frete'], 2, ',', '.') }}</td>
                <td class="text-right text-warning">R$ {{ number_format($totais['total_comissao'], 2, ',', '.') }}</td>
                <td class="text-right text-danger">R$ {{ number_format($totais['total_descontos'], 2, ',', '.') }}</td>
                <td class="text-right text-danger">R$ {{ number_format($totais['total_adiantamento'], 2, ',', '.') }}</td>
                <td class="text-right {{ $totais['total_saldo'] >= 0 ? 'text-success' : 'text-danger' }}">
                    R$ {{ number_format($totais['total_saldo'], 2, ',', '.') }}
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    {{-- Assinaturas --}}
    <div class="assinaturas">
        <div class="assinatura-box">
            <div class="assinatura-linha"></div>
            <div class="assinatura-nome">{{ $motorista->nome }}</div>
            <div class="assinatura-label">Motorista — CPF: {{ $motorista->cpf }}</div>
        </div>
        <div class="assinatura-box">
            <div class="assinatura-linha"></div>
            <div class="assinatura-nome">Responsável pela Transportadora</div>
            <div class="assinatura-label">Assinatura e Carimbo</div>
        </div>
    </div>

    <div class="footer">
        Invexa Frete — Acerto gerado em {{ now()->format('d/m/Y \à\s H:i') }}
    </div>

</body>
</html>