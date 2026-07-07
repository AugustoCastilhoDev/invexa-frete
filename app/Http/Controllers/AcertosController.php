<?php

namespace App\Http\Controllers;

use App\Models\Motorista;
use App\Models\Viagem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
            $viagens   = $this->viagensDoMotorista($motoristaSel, $dataInicio, $dataFim);
            $totais    = $this->calcularTotais($viagens);
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
        $viagens   = $this->viagensDoMotorista($motoristaSel, $dataInicio, $dataFim);
        $totais    = $this->calcularTotais($viagens);

        $pdf = Pdf::loadView('acertos.pdf', compact(
            'motorista', 'viagens', 'totais', 'dataInicio', 'dataFim'
        ))->setPaper('a4', 'portrait');

        return $pdf->stream('acerto-motorista-' . $motorista->id . '.pdf');
    }

    public function csv(Request $request): StreamedResponse
    {
        $motoristaSel = $request->input('motorista_id');
        $dataInicio   = $request->input('data_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dataFim      = $request->input('data_fim', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $motorista = Motorista::findOrFail($motoristaSel);
        $viagens   = $this->viagensDoMotorista($motoristaSel, $dataInicio, $dataFim);

        $nomeArquivo = 'acerto-' . str($motorista->nome)->slug() . '-' . $dataInicio . '-a-' . $dataFim . '.csv';

        return response()->streamDownload(function () use ($viagens) {
            $saida = fopen('php://output', 'w');
            fwrite($saida, "\xEF\xBB\xBF");

            fputcsv($saida, [
                'Viagem', 'Veículo', 'Cliente', 'Origem', 'Destino', 'Saída',
                'Frete', 'Comissão', 'Descontos', 'Bonificação', 'Saldo', 'Status',
            ], ';');

            foreach ($viagens as $viagem) {
                fputcsv($saida, [
                    $viagem->id,
                    $viagem->veiculo->placa,
                    $viagem->cliente->nome ?? '-',
                    $viagem->origem,
                    $viagem->destino,
                    $viagem->data_saida->format('d/m/Y'),
                    number_format($viagem->valor_frete, 2, ',', ''),
                    number_format($viagem->valor_motorista, 2, ',', ''),
                    number_format($viagem->total_descontos, 2, ',', ''),
                    number_format($viagem->total_bonificacoes, 2, ',', ''),
                    number_format($viagem->saldo_motorista, 2, ',', ''),
                    ucfirst(str_replace('_', ' ', $viagem->status)),
                ], ';');
            }

            fclose($saida);
        }, $nomeArquivo, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function viagensDoMotorista(int $motoristaId, string $dataInicio, string $dataFim)
    {
        return Viagem::with(['veiculo', 'cliente'])
            ->where('motorista_id', $motoristaId)
            ->whereBetween('data_saida', [$dataInicio, $dataFim])
            ->orderByDesc('data_saida')
            ->get();
    }

    private function calcularTotais($viagens): array
    {
        $totalKm     = $viagens->sum('km_rodados');
        $totalLitros = $viagens->sum('total_litros');

        return [
            'total_viagens'      => $viagens->count(),
            'total_frete'        => $viagens->sum('valor_frete'),
            'total_comissao'     => $viagens->sum('valor_motorista'),
            'total_descontos'    => $viagens->sum('total_descontos'),
            'total_bonificacoes' => $viagens->sum('total_bonificacoes'),
            'total_adiantamento' => $viagens->filter(fn($v) => $v->adiantamento_descontavel)
                                            ->sum('valor_adiantamento'),
            'total_saldo'        => $viagens->sum('saldo_motorista'),
            'total_km'           => $totalKm,
            'total_litros'       => $totalLitros,
            'media_combustivel'  => $totalLitros > 0 ? round($totalKm / $totalLitros, 2) : null,
            'por_status'         => $viagens->groupBy('status')->map->count(),
            'saldo_a_pagar'      => $viagens->whereNotIn('status', ['encerrada'])
                                            ->sum('saldo_motorista'),
            'saldo_pago'         => $viagens->where('status', 'encerrada')
                                            ->sum('saldo_motorista'),
            'viagens_abertas'    => $viagens->whereNotIn('status', ['encerrada'])->count(),
            'viagens_encerradas' => $viagens->where('status', 'encerrada')->count(),
        ];
    }
}
