<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\Viagem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DocumentosController extends Controller
{
    public function store(Request $request, Viagem $viagem)
    {
        // carga_id só é exigida quando a empresa tem Focus NFe ativo — é
        // nesse caso que a tela mostra o campo. Sem isso, o registro manual
        // de NF-e continua funcionando como sempre (nenhuma empresa hoje tem
        // o Focus ativo em produção, mas o teste de regressão cobre isso).
        $exigeCarga = $request->input('tipo') === 'nfe' && $viagem->empresa->focus_nfe_ativo;

        $request->validate([
            'tipo'          => 'required|in:cte,mdfe,nfe,outros',
            'carga_id'      => [Rule::requiredIf($exigeCarga), 'nullable', 'exists:cargas,id'],
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
                ->store('documentos', config('filesystems.uploads_disk'));
        }

        Documento::create($data);

        return redirect()->route('viagens.show', $viagem)
            ->with('success', 'Documento adicionado com sucesso!');
    }

    public function update(Request $request, Documento $documento)
    {
        $request->validate([
            'status'       => 'required|in:pendente,autorizado,cancelado',
            'chave_acesso' => 'nullable|string|max:60',
            'observacao'   => 'nullable|string',
        ]);

        $documento->update($request->only('status', 'chave_acesso', 'observacao'));

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