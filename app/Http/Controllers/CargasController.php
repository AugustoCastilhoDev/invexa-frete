<?php

namespace App\Http\Controllers;

use App\Models\Carga;
use App\Models\Viagem;
use Illuminate\Http\Request;

class CargasController extends Controller
{
    public function store(Request $request, Viagem $viagem)
    {
        $dados = $request->validate([
            'cliente_id'  => 'required|exists:clientes,id',
            'valor_frete' => 'nullable|numeric|min:0',
        ]);

        $dados['viagem_id'] = $viagem->id;

        Carga::create($dados);

        return redirect()->route('viagens.show', $viagem)
            ->with('success', 'Carga adicionada com sucesso!');
    }

    public function destroy(Carga $carga)
    {
        abort_if(
            $carga->documentos()->exists() || $carga->emissoesFiscais()->exists(),
            422,
            'Não é possível remover uma carga com documentos ou emissões fiscais vinculadas.'
        );

        $viagem = $carga->viagem;
        $carga->delete();

        return redirect()->route('viagens.show', $viagem)
            ->with('success', 'Carga removida com sucesso!');
    }
}
