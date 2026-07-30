<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\TabelaFrete;
use Illuminate\Http\Request;

class TabelasFreteController extends Controller
{
    public function store(Request $request, Cliente $cliente)
    {
        $dados = $request->validate([
            'origem' => 'required|string|max:255',
            'origem_uf' => 'required|string|max:2',
            'origem_codigo_municipio' => 'required|string|max:7',
            'destino' => 'required|string|max:255',
            'destino_uf' => 'required|string|max:2',
            'destino_codigo_municipio' => 'required|string|max:7',
            'valor_frete' => 'required|numeric|min:0',
        ]);

        $existe = $cliente->tabelasFrete()
            ->where('origem_codigo_municipio', $dados['origem_codigo_municipio'])
            ->where('destino_codigo_municipio', $dados['destino_codigo_municipio'])
            ->exists();

        if ($existe) {
            return back()->withErrors([
                'origem' => 'Já existe um valor de frete cadastrado para esta rota deste cliente.',
            ])->withInput();
        }

        $cliente->tabelasFrete()->create($dados);

        return back()->with('success', 'Rota adicionada à tabela de frete do cliente.');
    }

    public function destroy(TabelaFrete $tabelaFrete)
    {
        $tabelaFrete->delete();

        return back()->with('success', 'Rota removida da tabela de frete.');
    }

    // Usado via AJAX nas telas de viagem: sugere o valor cadastrado para a
    // rota (origem/destino identificados pelo código IBGE) do cliente
    // selecionado, se houver. O campo continua editável manualmente.
    public function sugestao(Request $request)
    {
        $dados = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'origem_codigo_municipio' => 'required|string',
            'destino_codigo_municipio' => 'required|string',
        ]);

        $valor = TabelaFrete::where('cliente_id', $dados['cliente_id'])
            ->where('origem_codigo_municipio', $dados['origem_codigo_municipio'])
            ->where('destino_codigo_municipio', $dados['destino_codigo_municipio'])
            ->value('valor_frete');

        return response()->json(['valor' => $valor]);
    }
}
