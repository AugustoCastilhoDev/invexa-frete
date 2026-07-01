<?php

namespace App\Http\Controllers;

use App\Models\Viagem;
use App\Models\Motorista;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class RelatorioController extends Controller
{
    public function index(Request $request)
    {
        $dataInicio  = $request->input('data_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dataFim     = $request->input('data_fim', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $motoristaSel = $request->input('motorista_id');
        $veiculoSel   = $request->input('veiculo_id');
        $statusSel    = $request->input('status', 'encerrada');

        $query = Viagem::with(['motorista', 'veiculo'])
            ->whereBetween('data_saida', [$dataInicio, $dataFim])
            ->orderByDesc('data_saida')
            ->orderByDesc('id');

        if ($motoristaSel) {
            $query->where('motorista_id', $motoristaSel);
        }

        if ($veiculoSel) {
            $query->where('veiculo_id', $veiculoSel);
        }

        if ($statusSel !== 'todos') {
            $query->where('status', $statusSel);
        }

        $viagens = $query->get();

        // ── Totalizadores ──
        $totais = [
            'frete'          => $viagens->sum('valor_frete'),
            'combustivel'    => $viagens->sum('total_combustivel'),
            'manutencao'     => $viagens->sum('total_manutencao'),
            'motoristas'     => $viagens->sum('valor_motorista'),
            'descontos'      => $viagens->sum('total_descontos'),
            'adiantamentos'  => $viagens->sum('valor_adiantamento'),
            'lucro'          => $viagens->sum('lucro_transportadora'),
            'saldo_motorista'=> $viagens->sum('saldo_motorista'),
            'total_viagens'  => $viagens->count(),
        ];

        // ── Agrupado por motorista ──
        $porMotoristaCollection = $viagens->groupBy('motorista_id')->map(function ($grupo) {
            return [
                'nome'       => $grupo->first()->motorista->nome,
                'viagens'    => $grupo->count(),
                'frete'      => $grupo->sum('valor_frete'),
                'comissao'   => $grupo->sum('valor_motorista'),
                'saldo'      => $grupo->sum('saldo_motorista'),
            ];
        })->sortByDesc('frete')->values();

        // ── Paginação manual da coleção ──
        $paginaMotorista = (int) $request->input('pagina_motorista', 1);
        $porPagina        = 5;

        $porMotorista = new \Illuminate\Pagination\LengthAwarePaginator(
            $porMotoristaCollection->forPage($paginaMotorista, $porPagina),
            $porMotoristaCollection->count(),
            $porPagina,
            $paginaMotorista,
            [
                'path'  => $request->url(),
                'query' => $request->except('pagina_motorista'),
                'pageName' => 'pagina_motorista',
            ]
        );

        $motoristas = Motorista::where('status', 'ativo')->orderBy('nome')->get();
        $veiculos   = Veiculo::where('status', 'ativo')->orderBy('placa')->get();

        return view('relatorios.index', compact(
            'viagens',
            'totais',
            'porMotorista',
            'motoristas',
            'veiculos',
            'dataInicio',
            'dataFim',
            'motoristaSel',
            'veiculoSel',
            'statusSel'
        ));
    }

    public function pdf(Request $request)
    {
        $dataInicio   = $request->input('data_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dataFim      = $request->input('data_fim', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $motoristaSel = $request->input('motorista_id');
        $veiculoSel   = $request->input('veiculo_id');
        $statusSel    = $request->input('status', 'encerrada');

        $query = Viagem::with(['motorista', 'veiculo'])
            ->whereBetween('data_saida', [$dataInicio, $dataFim])
            ->orderByDesc('data_saida')
            ->orderByDesc('id');

        if ($motoristaSel) $query->where('motorista_id', $motoristaSel);
        if ($veiculoSel)   $query->where('veiculo_id', $veiculoSel);
        if ($statusSel !== 'todos') $query->where('status', $statusSel);

        $viagens = $query->get();

        $totais = [
            'frete'          => $viagens->sum('valor_frete'),
            'combustivel'    => $viagens->sum('total_combustivel'),
            'manutencao'     => $viagens->sum('total_manutencao'),
            'motoristas'     => $viagens->sum('valor_motorista'),
            'descontos'      => $viagens->sum('total_descontos'),
            'adiantamentos'  => $viagens->sum('valor_adiantamento'),
            'lucro'          => $viagens->sum('lucro_transportadora'),
            'saldo_motorista'=> $viagens->sum('saldo_motorista'),
            'total_viagens'  => $viagens->count(),
        ];

        $porMotorista = $viagens->groupBy('motorista_id')->map(function ($grupo) {
            return [
                'nome'     => $grupo->first()->motorista->nome,
                'viagens'  => $grupo->count(),
                'frete'    => $grupo->sum('valor_frete'),
                'comissao' => $grupo->sum('valor_motorista'),
                'saldo'    => $grupo->sum('saldo_motorista'),
            ];
        })->sortByDesc('frete')->values();

        $pdf = Pdf::loadView('relatorios.pdf', compact(
            'viagens', 'totais', 'porMotorista',
            'dataInicio', 'dataFim', 'statusSel'
        ))->setPaper('a4', 'landscape');

        return $pdf->stream('relatorio-' . $dataInicio . '-a-' . $dataFim . '.pdf');
    }
}