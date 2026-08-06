<?php

namespace App\Http\Controllers;

use App\Models\Carga;
use App\Models\DestinoProgramacao;
use App\Models\Viagem;
use Illuminate\Http\Request;

class CargasController extends Controller
{
    public function store(Request $request, Viagem $viagem)
    {
        $dados = $request->validate([
            'cliente_id'                => 'required|exists:clientes,id',
            'unidade_id'                => 'nullable|exists:unidades,id',
            'valor_frete'               => 'nullable|numeric|min:0',
            'destino'                   => 'nullable|string|max:255',
            'destino_uf'                => 'nullable|string|max:2',
            'destino_codigo_municipio'  => 'nullable|string|max:7',
            'destino_programacao_id'    => 'nullable|exists:destinos_programacao,id',
        ]);

        $destinoProgramacao = null;
        if (! empty($dados['destino_programacao_id'])) {
            // Só aceita a sugestão se pertencer à programação que gerou esta
            // viagem — evita marcar como "convertido" um destino de outra
            // viagem/empresa só porque o id foi adivinhado na requisição.
            $destinoProgramacao = DestinoProgramacao::where('id', $dados['destino_programacao_id'])
                ->where('programacao_viagem_id', $viagem->programacao?->id)
                ->whereNull('carga_id')
                ->first();
        }
        unset($dados['destino_programacao_id']);

        $dados['viagem_id'] = $viagem->id;
        $dados['unidade_id'] = $dados['unidade_id'] ?? $viagem->unidade_id;

        $carga = Carga::create($dados);

        $destinoProgramacao?->forceFill(['carga_id' => $carga->id])->save();

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
