<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\Viagem;
use Illuminate\Http\Request;

class DocumentosController extends Controller
{
    public function store(Request $request, Viagem $viagem)
    {
        $request->validate([
            'tipo'          => 'required|in:cte,mdfe,nfe,outros',
            'numero'        => 'required|string|max:50',
            'chave_acesso'  => 'nullable|string|max:60',
            'serie'         => 'nullable|string|max:10',
            'data_emissao'  => 'required|date',
            'valor'         => 'required|numeric|min:0',
            'status'        => 'required|in:pendente,autorizado,cancelado',
            'arquivo'       => 'nullable|file|mimes:xml,pdf|max:5120',
            'observacao'    => 'nullable|string',
        ]);

        $data = $request->all();
        $data['viagem_id'] = $viagem->id;

        if ($request->hasFile('arquivo')) {
            $data['arquivo'] = $request->file('arquivo')
                ->store('documentos', 'public');
        }

        Documento::create($data);

        return redirect()->route('viagens.show', $viagem)
            ->with('success', 'Documento adicionado com sucesso!');
    }

    public function update(Request $request, Documento $documento)
    {
        $request->validate([
            'status'     => 'required|in:pendente,autorizado,cancelado',
            'observacao' => 'nullable|string',
        ]);

        $documento->update($request->only('status', 'observacao'));

        return redirect()->route('viagens.show', $documento->viagem)
            ->with('success', 'Documento atualizado com sucesso!');
    }

    public function destroy(Documento $documento)
    {
        $viagem = $documento->viagem;
        $documento->delete();

        return redirect()->route('viagens.show', $viagem)
            ->with('success', 'Documento removido com sucesso!');
    }
}