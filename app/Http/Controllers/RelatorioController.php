<?php

namespace App\Http\Controllers;

use App\Models\Viagem;
use App\Models\Motorista;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RelatorioController extends Controller
{
    public function index(Request $request)
    {
        ['viagens' => $viagens, 'totais' => $totais, 'dataInicio' => $dataInicio, 'dataFim' => $dataFim,
            'motoristaSel' => $motoristaSel, 'veiculoSel' => $veiculoSel, 'statusSel' => $statusSel]
            = $this->viagensFiltradas($request);

        $porMotoristaCollection = $this->agruparPorMotorista($viagens);

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
        ['viagens' => $viagens, 'totais' => $totais, 'dataInicio' => $dataInicio, 'dataFim' => $dataFim,
            'statusSel' => $statusSel] = $this->viagensFiltradas($request);

        $porMotorista = $this->agruparPorMotorista($viagens);

        $pdf = Pdf::loadView('relatorios.pdf', compact(
            'viagens', 'totais', 'porMotorista',
            'dataInicio', 'dataFim', 'statusSel'
        ))->setPaper('a4', 'landscape');

        return $pdf->stream('relatorio-' . $dataInicio . '-a-' . $dataFim . '.pdf');
    }

    public function csv(Request $request): StreamedResponse
    {
        ['viagens' => $viagens, 'dataInicio' => $dataInicio, 'dataFim' => $dataFim] = $this->viagensFiltradas($request);

        $nomeArquivo = 'relatorio-' . $dataInicio . '-a-' . $dataFim . '.csv';

        return response()->streamDownload(function () use ($viagens) {
            $saida = fopen('php://output', 'w');
            fwrite($saida, "\xEF\xBB\xBF"); // BOM para acentuação abrir corretamente no Excel

            fputcsv($saida, [
                'Viagem', 'Motorista', 'Veículo', 'Origem', 'Destino', 'Saída',
                'Frete', 'Combustível', 'Manutenção', 'Comissão Motorista',
                'Descontos', 'Lucro Transportadora', 'Status', 'Frete Recebido',
            ], ';');

            foreach ($viagens as $viagem) {
                fputcsv($saida, [
                    $viagem->id,
                    $viagem->motorista->nome,
                    $viagem->veiculo->placa,
                    $viagem->origem,
                    $viagem->destino,
                    $viagem->data_saida->format('d/m/Y'),
                    number_format($viagem->valor_frete, 2, ',', ''),
                    number_format($viagem->total_combustivel, 2, ',', ''),
                    number_format($viagem->total_manutencao, 2, ',', ''),
                    number_format($viagem->valor_motorista, 2, ',', ''),
                    number_format($viagem->total_descontos, 2, ',', ''),
                    number_format($viagem->lucro_transportadora, 2, ',', ''),
                    ucfirst(str_replace('_', ' ', $viagem->status)),
                    $viagem->frete_recebido ? 'Sim' : 'Não',
                ], ';');
            }

            fclose($saida);
        }, $nomeArquivo, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function viagensFiltradas(Request $request): array
    {
        $dataInicio   = $request->input('data_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dataFim      = $request->input('data_fim', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $motoristaSel = $request->input('motorista_id');
        $veiculoSel   = $request->input('veiculo_id');
        $statusSel    = $request->input('status', 'reconhecido');

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

        if ($statusSel === 'reconhecido') {
            // Encerradas, ou com frete já recebido mesmo que o acerto não
            // tenha fechado ainda — cada viagem entra uma única vez.
            $query->where(function ($q) {
                $q->where('status', 'encerrada')->orWhere('frete_recebido', true);
            });
        } elseif ($statusSel !== 'todos') {
            $query->where('status', $statusSel);
        }

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

        return compact('viagens', 'totais', 'dataInicio', 'dataFim', 'motoristaSel', 'veiculoSel', 'statusSel');
    }

    private function agruparPorMotorista($viagens)
    {
        return $viagens->groupBy('motorista_id')->map(function ($grupo) {
            return [
                'nome'     => $grupo->first()->motorista->nome,
                'viagens'  => $grupo->count(),
                'frete'    => $grupo->sum('valor_frete'),
                'comissao' => $grupo->sum('valor_motorista'),
                'saldo'    => $grupo->sum('saldo_motorista'),
            ];
        })->sortByDesc('frete')->values();
    }
}
