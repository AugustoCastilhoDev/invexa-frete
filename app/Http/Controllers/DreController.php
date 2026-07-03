<?php

namespace App\Http\Controllers;

use App\Models\DespesaGeral;
use App\Models\Manutencao;
use App\Models\Viagem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DreController extends Controller
{
    public function index(Request $request)
    {
        $dados = $this->calcular($request);

        return view('dre.index', $dados);
    }

    public function pdf(Request $request)
    {
        $dados = $this->calcular($request);

        $pdf = Pdf::loadView('dre.pdf', $dados)->setPaper('a4', 'portrait');

        return $pdf->stream('dre-' . $dados['dataInicio'] . '-a-' . $dados['dataFim'] . '.pdf');
    }

    private function calcular(Request $request): array
    {
        $dataInicio = $request->input('data_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dataFim    = $request->input('data_fim', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $viagens = Viagem::where('status', 'encerrada')
            ->whereBetween('data_saida', [$dataInicio, $dataFim])
            ->get();

        $receitaBruta        = $viagens->sum('valor_frete');
        $comissaoMotoristas   = $viagens->sum('valor_motorista');
        $combustivel          = $viagens->sum('total_combustivel');
        $manutencaoViagem     = $viagens->sum('total_manutencao');
        $custosDiretos        = $comissaoMotoristas + $combustivel + $manutencaoViagem;
        $resultadoBruto       = $receitaBruta - $custosDiretos;

        $manutencaoFrota = Manutencao::whereBetween('data_manutencao', [$dataInicio, $dataFim])->sum('valor');

        $despesasGeraisQuery = DespesaGeral::noPeriodo($dataInicio, $dataFim);
        $despesasGerais       = (clone $despesasGeraisQuery)->sum('valor');
        $despesasPorCategoria = (clone $despesasGeraisQuery)->get()
            ->groupBy('categoria')
            ->map(fn ($grupo) => [
                'rotulo' => $grupo->first()->categoria_formatada,
                'total'  => $grupo->sum('valor'),
            ])
            ->sortByDesc('total')
            ->values();

        $despesasOperacionais = $manutencaoFrota + $despesasGerais;
        $resultadoLiquido     = $resultadoBruto - $despesasOperacionais;

        return compact(
            'dataInicio', 'dataFim',
            'receitaBruta', 'comissaoMotoristas', 'combustivel', 'manutencaoViagem', 'custosDiretos', 'resultadoBruto',
            'manutencaoFrota', 'despesasGerais', 'despesasPorCategoria', 'despesasOperacionais', 'resultadoLiquido'
        );
    }
}
