<?php

namespace App\Http\Controllers;

use App\Models\Viagem;
use App\Models\Motorista;
use App\Models\Veiculo;
use App\Models\Documento;
use App\Models\Manutencao;
use App\Models\ProgramacaoViagem;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $hoje      = Carbon::now();
        $inicioMes = $hoje->copy()->startOfMonth();
        $fimMes    = $hoje->copy()->endOfMonth();

        // ── Cards operacionais ──
        // "Viagens Abertas" separado em dois: aberta (programada/criada, podendo
        // ou não já estar carregada) e em_andamento (já em rota) — antes um
        // único card somava os dois com legenda contraditória.
        $totalViagensEmAberto  = Viagem::where('status', 'aberta')->count();
        $totalViagensIniciadas = Viagem::where('status', 'em_andamento')->count();

        $totalViagensEncerradasMes = Viagem::where('status', 'encerrada')
            ->whereBetween('updated_at', [$inicioMes, $fimMes])
            ->count();

        $totalMotoristasAtivos = Motorista::where('status', 'ativo')->count();
        $totalVeiculosAtivos   = Veiculo::contamParaLimite()->where('status', 'ativo')->count();
        $totalAguardandoAcerto = Viagem::where('status', 'aguardando_acerto')->count();

        // ── Programação de Frota (mesmos cards da tela /programacoes) ──
        $veiculosComProgramacaoPendente = ProgramacaoViagem::where('status', 'pendente')->pluck('veiculo_id');
        $totalVeiculosProgramados = $veiculosComProgramacaoPendente->count();
        $totalVeiculosSemProgramacao = Veiculo::contamParaLimite()
            ->where('status', 'ativo')
            ->whereNotIn('id', $veiculosComProgramacaoPendente)
            ->count();
        $totalRiscoNoShow = ProgramacaoViagem::emRiscoDeNoShow()->count();

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
            'totalViagensEmAberto',
            'totalViagensIniciadas',
            'totalViagensEncerradasMes',
            'totalMotoristasAtivos',
            'totalVeiculosAtivos',
            'totalAguardandoAcerto',
            'totalVeiculosProgramados',
            'totalVeiculosSemProgramacao',
            'totalRiscoNoShow',
            'ultimasViagens',
            'topMotoristas',
            'viagensPorStatus',
            'cnhVencendo',
            'veiculosEmManutencao',
            'documentosPendentes',
            'manutencoesVencendo'
        ));
    }
}
