<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>DRE</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'DejaVu Sans',sans-serif; font-size:10px; color:#1a1a1a; padding:20px; }

        .header { display:flex; justify-content:space-between; align-items:flex-start;
                  border-bottom:3px solid #f97316; padding-bottom:10px; margin-bottom:15px; }
        .brand  { font-size:18px; font-weight:700; color:#1a1a2e; }
        .brand span { color:#f97316; }
        .doc-info { text-align:right; color:#6c757d; font-size:9px; }
        .doc-info h2 { font-size:13px; color:#1a1a2e; font-weight:700; }

        table { width:100%; border-collapse:collapse; margin-bottom:12px; }
        .linha { display:flex; justify-content:space-between; padding:5px 0; }
        .linha.titulo { font-weight:700; text-transform:uppercase; font-size:9px; color:#f97316;
                         border-bottom:1px solid #f0f0f0; margin-top:10px; }
        .linha.item { padding-left:14px; border-bottom:1px solid #f5f5f5; color:#92400e; }
        .linha.resultado { font-weight:700; font-size:12px; border-top:2px solid #e9ecef;
                            border-bottom:2px solid #e9ecef; margin-top:6px; padding:8px 0; }
        .verde  { color:#065f46; }
        .vermelho { color:#991b1b; }

        .section-title { font-size:9px; font-weight:700; text-transform:uppercase;
                         letter-spacing:1px; color:#f97316; border-bottom:1px solid #f0f0f0;
                         padding-bottom:4px; margin-bottom:8px; margin-top:16px; }

        th { padding:5px 6px; font-size:8px; font-weight:700; text-transform:uppercase;
             letter-spacing:.5px; color:#6c757d; text-align:left; border-bottom:1px solid #e9ecef; }
        td { padding:4px 6px; border-bottom:1px solid #f5f5f5; font-size:9px; }
        .text-right { text-align:right; }

        .footer { margin-top:15px; padding-top:8px; border-top:1px solid #e9ecef;
                  text-align:center; font-size:8px; color:#adb5bd; }
    </style>
</head>
<body>

    <div class="header">
        <div>
            <div class="brand">Invexa <span>Frete</span></div>
            <div style="font-size:9px;color:#6c757d">Demonstrativo de Resultado (DRE)</div>
        </div>
        <div class="doc-info">
            <h2>DRE Simplificado</h2>
            <div>Período: {{ \Carbon\Carbon::parse($dataInicio)->format('d/m/Y') }}
                 a {{ \Carbon\Carbon::parse($dataFim)->format('d/m/Y') }}</div>
            <div>Emitido em {{ now()->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    <div class="linha titulo"><span>Receita Bruta</span><span></span></div>
    <div class="linha item"><span>Faturamento (viagens encerradas)</span>
        <span>R$ {{ number_format($receitaBruta, 2, ',', '.') }}</span></div>

    <div class="linha titulo"><span>(-) Custos Diretos</span><span></span></div>
    <div class="linha item"><span>Comissão de motoristas</span>
        <span>R$ {{ number_format($comissaoMotoristas, 2, ',', '.') }}</span></div>
    <div class="linha item"><span>Combustível</span>
        <span>R$ {{ number_format($combustivel, 2, ',', '.') }}</span></div>
    <div class="linha item"><span>Manutenção (durante viagem)</span>
        <span>R$ {{ number_format($manutencaoViagem, 2, ',', '.') }}</span></div>

    <div class="linha resultado verde"><span>= Resultado Bruto</span>
        <span>R$ {{ number_format($resultadoBruto, 2, ',', '.') }}</span></div>

    <div class="linha titulo"><span>(-) Despesas Operacionais</span><span></span></div>
    <div class="linha item"><span>Manutenção de frota (fora de viagem)</span>
        <span>R$ {{ number_format($manutencaoFrota, 2, ',', '.') }}</span></div>
    <div class="linha item"><span>Despesas administrativas</span>
        <span>R$ {{ number_format($despesasGerais, 2, ',', '.') }}</span></div>

    <div class="linha resultado {{ $resultadoLiquido >= 0 ? 'verde' : 'vermelho' }}">
        <span>= Resultado Líquido</span>
        <span>R$ {{ number_format($resultadoLiquido, 2, ',', '.') }}</span></div>

    <div class="section-title">Despesas Administrativas por Categoria</div>
    <table>
        <thead>
            <tr><th>Categoria</th><th class="text-right">Total</th></tr>
        </thead>
        <tbody>
            @forelse($despesasPorCategoria as $item)
            <tr>
                <td>{{ $item['rotulo'] }}</td>
                <td class="text-right">R$ {{ number_format($item['total'], 2, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="2">Nenhuma despesa administrativa no período.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Invexa Frete — DRE gerado em {{ now()->format('d/m/Y \à\s H:i') }}
    </div>

</body>
</html>
