<?php

namespace App\Http\Controllers;

use App\Models\Manutencao;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManutencoesController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->manutencoesFiltradas($request);

        $totalGasto     = (clone $query)->sum('valor');
        $totalRegistros = (clone $query)->count();

        $manutencoes = $query->orderByDesc('data_manutencao')->orderByDesc('id')
            ->paginate(10)->withQueryString();

        $veiculos = Veiculo::orderBy('placa')->get();

        return view('manutencoes.index', compact('manutencoes', 'veiculos', 'totalGasto', 'totalRegistros'));
    }

    public function csv(Request $request): StreamedResponse
    {
        $manutencoes = $this->manutencoesFiltradas($request)
            ->orderByDesc('data_manutencao')->orderByDesc('id')->get();

        return response()->streamDownload(function () use ($manutencoes) {
            $saida = fopen('php://output', 'w');
            fwrite($saida, "\xEF\xBB\xBF"); // BOM para acentuação abrir corretamente no Excel

            fputcsv($saida, [
                'Veículo', 'Tipo', 'Descrição', 'Data', 'KM', 'Valor',
                'Próxima (Data)', 'Próxima (KM)', 'Status', 'Observação',
            ], ';');

            foreach ($manutencoes as $manutencao) {
                fputcsv($saida, [
                    $manutencao->veiculo->placa,
                    ucfirst($manutencao->tipo),
                    $manutencao->descricao,
                    $manutencao->data_manutencao->format('d/m/Y'),
                    $manutencao->km_veiculo ? number_format($manutencao->km_veiculo, 0, ',', '.') : '',
                    number_format($manutencao->valor, 2, ',', ''),
                    optional($manutencao->proxima_manutencao_data)->format('d/m/Y'),
                    $manutencao->proxima_manutencao_km ? number_format($manutencao->proxima_manutencao_km, 0, ',', '.') : '',
                    $manutencao->status === 'concluida' ? 'Concluída' : 'Em andamento',
                    $manutencao->observacao,
                ], ';');
            }

            fclose($saida);
        }, 'historico-manutencoes.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function manutencoesFiltradas(Request $request)
    {
        return Manutencao::with('veiculo')
            ->when($request->input('veiculo_id'), fn ($q, $v) => $q->where('veiculo_id', $v))
            ->when($request->input('tipo'), fn ($q, $v) => $q->where('tipo', $v))
            ->when($request->input('status'), fn ($q, $v) => $q->where('status', $v))
            ->when($request->input('data_inicio'), fn ($q, $v) => $q->whereDate('data_manutencao', '>=', $v))
            ->when($request->input('data_fim'), fn ($q, $v) => $q->whereDate('data_manutencao', '<=', $v));
    }

    public function store(Request $request, Veiculo $veiculo)
    {
        $request->validate([
            'tipo'                     => 'required|in:preventiva,corretiva',
            'descricao'                => 'required|string|max:255',
            'data_manutencao'          => 'required|date',
            'km_veiculo'               => 'nullable|integer|min:0',
            'valor'                    => 'required|numeric|min:0',
            'proxima_manutencao_data'  => 'nullable|date|after:data_manutencao',
            'proxima_manutencao_km'    => 'nullable|integer|min:0',
            'status'                   => 'required|in:em_andamento,concluida',
            'observacao'               => 'nullable|string',
        ]);

        $data = $request->all();
        $data['veiculo_id'] = $veiculo->id;

        Manutencao::create($data);

        return redirect()->route('veiculos.show', $veiculo)
            ->with('success', 'Manutenção registrada com sucesso!');
    }

    public function update(Request $request, Manutencao $manutencao)
    {
        $request->validate([
            'status'                   => 'required|in:em_andamento,concluida',
            'valor'                    => 'nullable|numeric|min:0',
            'km_veiculo'               => 'nullable|integer|min:0',
            'proxima_manutencao_data'  => 'nullable|date',
            'proxima_manutencao_km'    => 'nullable|integer|min:0',
            'observacao'               => 'nullable|string',
        ]);

        $manutencao->update($request->only([
            'status', 'valor', 'km_veiculo',
            'proxima_manutencao_data', 'proxima_manutencao_km', 'observacao',
        ]));

        return redirect()->route('veiculos.show', $manutencao->veiculo)
            ->with('success', 'Manutenção atualizada com sucesso!');
    }

    public function destroy(Manutencao $manutencao)
    {
        $veiculo = $manutencao->veiculo;
        $manutencao->delete();

        return redirect()->route('veiculos.show', $veiculo)
            ->with('success', 'Manutenção removida com sucesso!');
    }
}
