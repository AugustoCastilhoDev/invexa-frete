<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Unidade;
use Illuminate\Http\Request;

class UnidadesController extends Controller
{
    private const REGRAS = [
        'nome' => 'required|string|max:255',
        'cnpj' => 'nullable|string|max:20',
        'inscricao_estadual' => 'nullable|string|max:30',
        'cep' => 'nullable|string|max:9',
        'logradouro' => 'nullable|string|max:255',
        'numero' => 'nullable|string|max:20',
        'complemento' => 'nullable|string|max:255',
        'bairro' => 'nullable|string|max:255',
        'municipio' => 'nullable|string|max:255',
        'codigo_municipio' => 'nullable|string|max:7',
        'uf' => 'nullable|string|max:2',
        'telefone' => 'nullable|string|max:20',
        'rntrc' => 'nullable|string|max:20',
        'cfop_padrao' => 'nullable|string|max:10',
        'icms_situacao_tributaria' => 'nullable|string|max:10',
        'icms_aliquota' => 'nullable|numeric|min:0|max:100',
    ];

    public function store(Request $request, Empresa $empresa)
    {
        $dados = $request->validate(self::REGRAS);

        $empresa->unidades()->create($dados);

        return redirect()->route('empresas.show', $empresa)
            ->with('success', 'Unidade adicionada com sucesso!');
    }

    public function update(Request $request, Unidade $unidade)
    {
        $dados = $request->validate(self::REGRAS);

        $unidade->update($dados);

        return redirect()->route('empresas.show', $unidade->empresa)
            ->with('success', 'Unidade atualizada com sucesso!');
    }

    public function destroy(Unidade $unidade)
    {
        abort_if(
            $unidade->cargas()->exists() || $unidade->viagens()->exists(),
            422,
            'Não é possível remover uma unidade referenciada por cargas ou viagens.'
        );

        $empresa = $unidade->empresa;
        $unidade->delete();

        return redirect()->route('empresas.show', $empresa)
            ->with('success', 'Unidade removida com sucesso!');
    }
}
