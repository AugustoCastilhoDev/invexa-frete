<?php

namespace App\Http\Controllers;

use App\Models\Lancamento;
use App\Models\Viagem;
use Illuminate\Http\Request;

class LancamentosController extends Controller
{
    public function store(Request $request, Viagem $viagem)
    {
        $request->validate([
            'tipo'             => 'required|in:combustivel,manutencao,outros',
            'descricao'        => 'required|string|max:255',
            'valor'            => 'required|numeric|min:0',
            'km_veiculo'       => 'nullable|integer|min:0',
            'litros'           => 'nullable|numeric|min:0',
            'valor_litro'      => 'nullable|numeric|min:0',
            'data_lancamento'  => 'required|date',
            'observacao'       => 'nullable|string',
            'comprovante'      => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $data = $request->all();
        $data['viagem_id'] = $viagem->id;

        if ($request->hasFile('comprovante')) {
            $data['comprovante'] = $request->file('comprovante')
                ->store('comprovantes', config('filesystems.uploads_disk'));
        }

        $lancamento = new Lancamento($data);
        $lancamento->status = 'aprovado';
        $lancamento->save();

        return redirect()->route('viagens.show', $viagem)
            ->with('success', 'Lançamento adicionado com sucesso!');
    }

    public function aprovar(Lancamento $lancamento)
    {
        $lancamento->forceFill(['status' => 'aprovado'])->save();

        return redirect()->route('viagens.show', $lancamento->viagem)
            ->with('success', 'Lançamento aprovado com sucesso!');
    }

    public function rejeitar(Lancamento $lancamento)
    {
        $lancamento->forceFill(['status' => 'rejeitado'])->save();

        return redirect()->route('viagens.show', $lancamento->viagem)
            ->with('success', 'Lançamento rejeitado.');
    }

    public function destroy(Lancamento $lancamento)
    {
        $viagem = $lancamento->viagem;
        $lancamento->delete();

        return redirect()->route('viagens.show', $viagem)
            ->with('success', 'Lançamento removido com sucesso!');
    }
}