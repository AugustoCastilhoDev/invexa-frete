<?php

namespace App\Http\Controllers;

use App\Models\Veiculo;
use Illuminate\Http\Request;

class VeiculosController extends Controller
{
    public function index(Request $request)
    {
        $busca = $request->input('busca');

        $veiculos = Veiculo::when($busca, function ($query) use ($busca) {
                $query->where('placa', 'like', "%{$busca}%")
                    ->orWhere('modelo', 'like', "%{$busca}%")
                    ->orWhere('marca', 'like', "%{$busca}%");
            })
            ->orderBy('placa')
            ->paginate(15)
            ->withQueryString();

        return view('veiculos.index', compact('veiculos', 'busca'));
    }

    public function create()
    {
        return view('veiculos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'placa'        => 'required|string|max:10|unique:veiculos',
            'modelo'       => 'required|string|max:255',
            'marca'        => 'nullable|string|max:255',
            'ano'          => 'nullable|integer|min:1990|max:' . (date('Y') + 1),
            'tipo'         => 'required|in:truck,carreta,van,utilitario,outro',
            'renavam'      => 'nullable|string|max:20',
            'capacidade_kg'=> 'nullable|numeric|min:0',
            'status'       => 'required|in:ativo,inativo,manutencao',
        ]);

        Veiculo::create($request->all());

        return redirect()->route('veiculos.index')
            ->with('success', 'Veículo cadastrado com sucesso!');
    }

    public function show(Veiculo $veiculo)
    {
        $viagens = $veiculo->viagens()->orderByDesc('data_saida')->paginate(10);
        return view('veiculos.show', compact('veiculo', 'viagens'));
    }

    public function edit(Veiculo $veiculo)
    {
        return view('veiculos.edit', compact('veiculo'));
    }

    public function update(Request $request, Veiculo $veiculo)
    {
        $request->validate([
            'placa'        => 'required|string|max:10|unique:veiculos,placa,' . $veiculo->id,
            'modelo'       => 'required|string|max:255',
            'marca'        => 'nullable|string|max:255',
            'ano'          => 'nullable|integer|min:1990|max:' . (date('Y') + 1),
            'tipo'         => 'required|in:truck,carreta,van,utilitario,outro',
            'renavam'      => 'nullable|string|max:20',
            'capacidade_kg'=> 'nullable|numeric|min:0',
            'status'       => 'required|in:ativo,inativo,manutencao',
        ]);

        $veiculo->update($request->all());

        return redirect()->route('veiculos.index')
            ->with('success', 'Veículo atualizado com sucesso!');
    }

    public function destroy(Veiculo $veiculo)
    {
        $veiculo->delete();
        return redirect()->route('veiculos.index')
            ->with('success', 'Veículo removido com sucesso!');
    }
}