<?php

namespace App\Http\Controllers;

use App\Models\Desconto;
use App\Models\Viagem;
use Illuminate\Http\Request;

class DescontosController extends Controller
{
    public function store(Request $request, Viagem $viagem)
    {
        $request->validate([
            'tipo'          => 'required|in:vale,multa,adiantamento,outros',
            'descricao'     => 'required|string|max:255',
            'valor'         => 'required|numeric|min:0',
            'data_desconto' => 'required|date',
            'observacao'    => 'nullable|string',
        ]);

        $data = $request->all();
        $data['viagem_id'] = $viagem->id;

        Desconto::create($data);

        return redirect()->route('viagens.show', $viagem)
            ->with('success', 'Desconto adicionado com sucesso!');
    }

    public function destroy(Desconto $desconto)
    {
        $viagem = $desconto->viagem;
        $desconto->delete();

        return redirect()->route('viagens.show', $viagem)
            ->with('success', 'Desconto removido com sucesso!');
    }
}