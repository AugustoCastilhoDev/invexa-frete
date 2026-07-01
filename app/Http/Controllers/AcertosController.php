<?php

namespace App\Http\Controllers;

use App\Models\Motorista;
use App\Models\Viagem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class AcertosController extends Controller
{
    public function index(Request $request)
    {
        $motoristaSel = $request->input('motorista_id');
        $dataInicio   = $request->input('data_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dataFim      = $request->input('data_fim', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $motoristas = Motorista::where('status', 'ativo')->orderBy('nome')->get();

        $viagens   = collect();
        $totais    = [];
        $motorista = null;

        if ($motoristaSel) {
            $motorista = Motorista::findOrFail($motoristaSel);

            $viagens = Viagem::with(['veiculo', 'cliente'])
                ->where('motorista_id', $motoristaSel)
                ->whereBetween('data_saida', [$dataInicio, $dataFim])
                ->orderByDesc('data_saida')
                ->get();

            $totais = [
                'total_viagens'      => $viagens->count(),
                'total_frete'        => $viagens->sum('valor_frete'),
                'total_comissao'     => $viagens->sum('valor_motorista'),
                'total_descontos'    => $viagens->sum('total_descontos'),
                'total_adiantamento' => $viagens->filter(fn($v) => $v->adiantamento_descontavel)
                                                ->sum('valor_adiantamento'),
                'total_saldo'        => $viagens->sum('saldo_motorista'),
                'total_km'           => $viagens->sum('km_rodados'),
                'por_status'         => $viagens->groupBy('status')->map->count(),
                'saldo_a_pagar'      => $viagens->whereNotIn('status', ['encerrada'])
                                                ->sum('saldo_motorista'),
                'saldo_pago'         => $viagens->where('status', 'encerrada')
                                                ->sum('saldo_motorista'),
                'viagens_abertas'    => $viagens->whereNotIn('status', ['encerrada'])->count(),
                'viagens_encerradas' => $viagens->where('status', 'encerrada')->count(),
            ];
        }

        return view('acertos.index', compact(
            'motoristas',
            'motorista',
            'viagens',
            'totais',
            'motoristaSel',
            'dataInicio',
            'dataFim'
        ));
    }

    public function pdf(Request $request)
    {
        $motoristaSel = $request->input('motorista_id');
        $dataInicio   = $request->input('data_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dataFim      = $request->input('data_fim', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $motorista = Motorista::findOrFail($motoristaSel);

        $viagens = Viagem::with(['veiculo', 'cliente'])
            ->where('motorista_id', $motoristaSel)
            ->whereBetween('data_saida', [$dataInicio, $dataFim])
            ->orderByDesc('data_saida')
            ->get();

        $totais = [
            'total_viagens'      => $viagens->count(),
            'total_frete'        => $viagens->sum('valor_frete'),
            'total_comissao'     => $viagens->sum('valor_motorista'),
            'total_descontos'    => $viagens->sum('total_descontos'),
            'total_adiantamento' => $viagens->filter(fn($v) => $v->adiantamento_descontavel)
                                            ->sum('valor_adiantamento'),
            'total_saldo'        => $viagens->sum('saldo_motorista'),
            'total_km'           => $viagens->sum('km_rodados'),
            'saldo_a_pagar'      => $viagens->whereNotIn('status', ['encerrada'])
                                            ->sum('saldo_motorista'),
            'saldo_pago'         => $viagens->where('status', 'encerrada')
                                            ->sum('saldo_motorista'),
            'viagens_abertas'    => $viagens->whereNotIn('status', ['encerrada'])->count(),
            'viagens_encerradas' => $viagens->where('status', 'encerrada')->count(),
        ];

        $pdf = Pdf::loadView('acertos.pdf', compact(
            'motorista', 'viagens', 'totais', 'dataInicio', 'dataFim'
        ))->setPaper('a4', 'portrait');

        return $pdf->stream('acerto-motorista-' . $motorista->id . '.pdf');
    }
}