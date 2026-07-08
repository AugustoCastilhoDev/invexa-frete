<?php

namespace App\Http\Controllers;

use App\Models\Viagem;
use App\Models\Motorista;
use App\Models\Veiculo;
use App\Models\Documento;
use App\Models\Manutencao;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $hoje      = Carbon::now();
        $inicioMes = $hoje->copy()->startOfMonth();
        $fimMes    = $hoje->copy()->endOfMonth();

        // ── Cards de resumo ──
        $totalViagensAbertas = Viagem::whereIn('status', ['aberta', 'em_andamento'])->count();

        $totalViagensEncerradasMes = Viagem::where('status', 'encerrada')
            ->whereBetween('updated_at', [$inicioMes, $fimMes])
            ->count();

        // Reconhece o faturamento pela data do recebimento do frete quando já
        // confirmado (mesmo com a viagem ainda aberta); senão, pela data de
        // encerramento — cada viagem cai em um único mês, nunca nos dois.
        $faturamentoMes = $this->viagensComFaturamentoReconhecido($inicioMes, $fimMes)->sum('valor_frete');
        $lucroMes       = $this->viagensComFaturamentoReconhecido($inicioMes, $fimMes)->sum('lucro_transportadora');

        $totalMotoristasAtivos = Motorista::where('status', 'ativo')->count();
        $totalVeiculosAtivos   = Veiculo::contamParaLimite()->where('status', 'ativo')->count();
        $totalAguardandoAcerto = Viagem::where('status', 'aguardando_acerto')->count();

        // ── Últimas viagens abertas ──
        $ultimasViagens = Viagem::with(['motorista', 'veiculo'])
            ->whereIn('status', ['aberta', 'em_andamento', 'aguardando_acerto'])
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        // ── Top 5 motoristas do mês ──
        $topMotoristas = Viagem::with('motorista')
            ->where('status', 'encerrada')
            ->whereBetween('updated_at', [$inicioMes, $fimMes])
            ->selectRaw('motorista_id, SUM(valor_frete) as total_frete, SUM(valor_motorista) as total_comissao, COUNT(*) as total_viagens')
            ->groupBy('motorista_id')
            ->orderByDesc('total_frete')
            ->take(5)
            ->get();

        // ── Viagens por status ──
        $viagensPorStatus = Viagem::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // ── Pendências operacionais ──
        $cnhVencendo = Motorista::cnhVencendo()
            ->orderBy('validade_cnh')
            ->get();

        $veiculosEmManutencao = Veiculo::emManutencao()
            ->orderBy('placa')
            ->get();

        $documentosPendentes = Documento::pendentes()
            ->with('viagem')
            ->orderBy('data_emissao')
            ->take(10)
            ->get();

        $manutencoesVencendo = Manutencao::with('veiculo')
            ->proximasVencendo()
            ->orderBy('proxima_manutencao_data')
            ->get();

        return view('dashboard', compact(
            'totalViagensAbertas',
            'totalViagensEncerradasMes',
            'faturamentoMes',
            'lucroMes',
            'totalMotoristasAtivos',
            'totalVeiculosAtivos',
            'totalAguardandoAcerto',
            'ultimasViagens',
            'topMotoristas',
            'viagensPorStatus',
            'cnhVencendo',
            'veiculosEmManutencao',
            'documentosPendentes',
            'manutencoesVencendo'
        ));
    }

    // Viagens cujo faturamento já deve ser reconhecido no período: pela data do
    // recebimento do frete quando confirmado (mesmo com a viagem ainda aberta),
    // ou pela data de encerramento — cada viagem entra em um único período.
    private function viagensComFaturamentoReconhecido($inicio, $fim)
    {
        return Viagem::where(function ($q) use ($inicio, $fim) {
            $q->where('frete_recebido', true)
                ->whereBetween('data_recebimento_frete', [$inicio, $fim]);
        })->orWhere(function ($q) use ($inicio, $fim) {
            $q->where('status', 'encerrada')
                ->where('frete_recebido', false)
                ->whereBetween('updated_at', [$inicio, $fim]);
        });
    }

    public function grafico(Request $request)
    {
        $tipo    = $request->input('tipo', '30');
        $dataFim = Carbon::now()->endOfDay();

        switch ($tipo) {
            case '30':
                $dataInicio = Carbon::now()->subDays(30)->startOfDay();
                break;
            case '60':
                $dataInicio = Carbon::now()->subDays(60)->startOfDay();
                break;
            case '90':
                $dataInicio = Carbon::now()->subDays(90)->startOfDay();
                break;
            case 'personalizado':
                $dataInicio = Carbon::parse($request->input('inicio'))->startOfDay();
                $dataFim    = Carbon::parse($request->input('fim'))->endOfDay();
                break;
            default:
                $dataInicio = Carbon::now()->subDays(30)->startOfDay();
        }

        $dados = collect();

        if (in_array($tipo, ['30', '60'])) {
            // Agrupado por dia
            $dia = $dataInicio->copy();
            while ($dia->lte($dataFim)) {
                $dados->push([
                    'label' => $dia->format('d/m'),
                    'frete' => $this->viagensComFaturamentoReconhecido($dia->copy()->startOfDay(), $dia->copy()->endOfDay())
                        ->sum('valor_frete'),
                    'lucro' => $this->viagensComFaturamentoReconhecido($dia->copy()->startOfDay(), $dia->copy()->endOfDay())
                        ->sum('lucro_transportadora'),
                ]);
                $dia->addDay();
            }
        } elseif ($tipo === '90') {
            // Agrupado por semana
            $semana = $dataInicio->copy()->startOfWeek();
            while ($semana->lte($dataFim)) {
                $inicioSemana = $semana->copy()->startOfWeek();
                $fimSemana    = $semana->copy()->endOfWeek();
                $dados->push([
                    'label' => $inicioSemana->format('d/m') . '-' . $fimSemana->format('d/m'),
                    'frete' => $this->viagensComFaturamentoReconhecido($inicioSemana, $fimSemana)->sum('valor_frete'),
                    'lucro' => $this->viagensComFaturamentoReconhecido($inicioSemana, $fimSemana)->sum('lucro_transportadora'),
                ]);
                $semana->addWeek();
            }
        } else {
            // Personalizado — agrupado por mês
            $mes = $dataInicio->copy()->startOfMonth();
            while ($mes->lte($dataFim)) {
                $inicioMes = $mes->copy()->startOfMonth();
                $fimMes    = $mes->copy()->endOfMonth();
                $dados->push([
                    'label' => $mes->format('M/y'),
                    'frete' => $this->viagensComFaturamentoReconhecido($inicioMes, $fimMes)->sum('valor_frete'),
                    'lucro' => $this->viagensComFaturamentoReconhecido($inicioMes, $fimMes)->sum('lucro_transportadora'),
                ]);
                $mes->addMonth();
            }
        }

        return response()->json([
            'labels' => $dados->pluck('label'),
            'fretes' => $dados->pluck('frete'),
            'lucros' => $dados->pluck('lucro'),
            'totais' => [
                'frete' => $dados->sum('frete'),
                'lucro' => $dados->sum('lucro'),
            ],
        ]);
    }
}