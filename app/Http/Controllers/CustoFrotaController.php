<?php

namespace App\Http\Controllers;

use App\Models\DespesaGeral;
use App\Models\Manutencao;
use App\Models\Veiculo;
use App\Models\Viagem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustoFrotaController extends Controller
{
    public function index(Request $request)
    {
        $dados = $this->calcular($request);
        $dados['tendencia'] = $this->tendencia();

        return view('custo-frota.index', $dados);
    }

    public function csv(Request $request): StreamedResponse
    {
        $linhas = $this->calcular($request)['linhas'];

        return response()->streamDownload(function () use ($linhas) {
            $saida = fopen('php://output', 'w');
            fwrite($saida, "\xEF\xBB\xBF");

            fputcsv($saida, [
                'Veículo', 'KM Rodado', 'Custo Direto', 'Manutenção Avulsa', 'Custo Fixo Direto',
                'Overhead Rateado', 'Custo Total', 'R$/km', 'Receita R$/km', 'Margem R$/km',
            ], ';');

            foreach ($linhas as $linha) {
                fputcsv($saida, [
                    $linha['veiculo']->placa,
                    number_format($linha['kmRodados'], 0, ',', '.'),
                    number_format($linha['custoDireto'], 2, ',', ''),
                    number_format($linha['manutencaoAvulsa'], 2, ',', ''),
                    number_format($linha['custoFixoDireto'], 2, ',', ''),
                    number_format($linha['overheadRateado'], 2, ',', ''),
                    number_format($linha['custoTotal'], 2, ',', ''),
                    $linha['custoPorKm'] !== null ? number_format($linha['custoPorKm'], 2, ',', '') : '',
                    $linha['receitaPorKm'] !== null ? number_format($linha['receitaPorKm'], 2, ',', '') : '',
                    $linha['margemPorKm'] !== null ? number_format($linha['margemPorKm'], 2, ',', '') : '',
                ], ';');
            }

            fclose($saida);
        }, 'custo-frota.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Custo por veículo no período, cruzando lançamentos de viagem
     * (variável), manutenção avulsa e despesas gerais (fixo, direto quando a
     * despesa tem veiculo_id, senão rateado pelos dias em que cada veículo
     * esteve na frota dentro do período — não por KM, pra não penalizar o
     * veículo mais produtivo).
     */
    private function calcular(Request $request): array
    {
        $dataInicio = $request->input('data_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dataFim    = $request->input('data_fim', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $periodoInicio = Carbon::parse($dataInicio)->startOfDay();
        $periodoFim    = Carbon::parse($dataFim)->startOfDay();

        $veiculos = Veiculo::contamParaLimite()
            ->where('status', '!=', 'inativo')
            ->where('created_at', '<=', $dataFim)
            ->orderBy('placa')
            ->get();

        $linhas = $veiculos->map(
            fn (Veiculo $veiculo) => $this->custosDoVeiculo($veiculo, $dataInicio, $dataFim, $periodoInicio, $periodoFim)
        );

        $overheadTotal   = (float) DespesaGeral::whereNull('veiculo_id')->noPeriodo($dataInicio, $dataFim)->sum('valor');
        $totalDiasAtivos = $linhas->sum('diasAtivos');

        $linhas = $linhas->map(function (array $linha) use ($overheadTotal, $totalDiasAtivos) {
            $linha['overheadRateado'] = $totalDiasAtivos > 0
                ? round($overheadTotal * ($linha['diasAtivos'] / $totalDiasAtivos), 2)
                : 0.0;

            $linha['custoTotal'] = $linha['custoDireto'] + $linha['manutencaoAvulsa']
                + $linha['custoFixoDireto'] + $linha['overheadRateado'];

            $linha['custoPorKm']   = $linha['kmRodados'] > 0 ? round($linha['custoTotal'] / $linha['kmRodados'], 2) : null;
            $linha['receitaPorKm'] = $linha['kmRodados'] > 0 ? round($linha['receita'] / $linha['kmRodados'], 2) : null;
            $linha['margemPorKm']  = ($linha['custoPorKm'] !== null && $linha['receitaPorKm'] !== null)
                ? round($linha['receitaPorKm'] - $linha['custoPorKm'], 2)
                : null;

            return $linha;
        })->sortByDesc(fn (array $linha) => $linha['custoPorKm'] ?? -INF)->values();

        $comKm = $linhas->filter(fn (array $l) => $l['custoPorKm'] !== null);

        $custoTotalFrota   = $linhas->sum('custoTotal');
        $kmTotalFrota      = $linhas->sum('kmRodados');
        $custoFixoTotal    = $linhas->sum('custoFixoDireto') + $overheadTotal;

        return [
            'dataInicio' => $dataInicio,
            'dataFim'    => $dataFim,
            'linhas'     => $linhas,
            'custoMedioFrota'      => $kmTotalFrota > 0 ? round($custoTotalFrota / $kmTotalFrota, 2) : null,
            'percentualCustoFixo'  => $custoTotalFrota > 0 ? round(($custoFixoTotal / $custoTotalFrota) * 100, 1) : 0.0,
            'custoFixoTotal'       => $custoFixoTotal,
            'custoTotalFrota'      => $custoTotalFrota,
            'veiculoMaisCaro'      => $comKm->sortByDesc('custoPorKm')->first(),
            'veiculoMaisEficiente' => $comKm->sortBy('custoPorKm')->first(),
        ];
    }

    private function custosDoVeiculo(
        Veiculo $veiculo,
        string $dataInicio,
        string $dataFim,
        Carbon $periodoInicio,
        Carbon $periodoFim
    ): array {
        $viagens = Viagem::where('veiculo_id', $veiculo->id)
            ->where('status', 'encerrada')
            ->whereBetween('data_saida', [$dataInicio, $dataFim])
            ->get();

        $manutencaoAvulsa = Manutencao::where('veiculo_id', $veiculo->id)
            ->whereBetween('data_manutencao', [$dataInicio, $dataFim])
            ->sum('valor');

        $custoFixoDireto = DespesaGeral::where('veiculo_id', $veiculo->id)
            ->noPeriodo($dataInicio, $dataFim)
            ->sum('valor');

        $inicioAtivo = max($periodoInicio, $veiculo->created_at->copy()->startOfDay());
        $diasAtivos  = $inicioAtivo->diffInDays($periodoFim) + 1;

        return [
            'veiculo'          => $veiculo,
            'kmRodados'        => $viagens->sum('km_rodados'),
            'custoDireto'      => (float) ($viagens->sum('total_combustivel') + $viagens->sum('total_manutencao')),
            'manutencaoAvulsa' => (float) $manutencaoAvulsa,
            'custoFixoDireto'  => (float) $custoFixoDireto,
            'receita'          => (float) $viagens->sum('valor_frete'),
            'diasAtivos'       => $diasAtivos,
        ];
    }

    /**
     * Custo x receita por km da frota inteira (sem detalhar por veículo),
     * mês a mês, para o gráfico de tendência.
     */
    private function tendencia(): array
    {
        return collect(range(5, 0))->map(function (int $i) {
            $mes    = Carbon::now()->subMonths($i);
            $inicio = $mes->copy()->startOfMonth()->format('Y-m-d');
            $fim    = $mes->copy()->endOfMonth()->format('Y-m-d');

            $viagens = Viagem::where('status', 'encerrada')
                ->whereBetween('data_saida', [$inicio, $fim])
                ->get();

            $km = $viagens->sum('km_rodados');
            $custoDireto      = $viagens->sum('total_combustivel') + $viagens->sum('total_manutencao');
            $manutencaoAvulsa = Manutencao::whereBetween('data_manutencao', [$inicio, $fim])->sum('valor');
            $despesasGerais   = DespesaGeral::noPeriodo($inicio, $fim)->sum('valor');
            $custoTotal       = $custoDireto + $manutencaoAvulsa + $despesasGerais;
            $receita          = $viagens->sum('valor_frete');

            return [
                'label'        => $mes->translatedFormat('M/y'),
                'custoPorKm'   => $km > 0 ? round($custoTotal / $km, 2) : null,
                'receitaPorKm' => $km > 0 ? round($receita / $km, 2) : null,
            ];
        })->all();
    }
}
