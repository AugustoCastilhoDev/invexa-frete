<?php

namespace App\Http\Controllers;

use App\Models\Manutencao;
use App\Models\Veiculo;
use Illuminate\Http\Request;

class ManutencoesController extends Controller
{
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
