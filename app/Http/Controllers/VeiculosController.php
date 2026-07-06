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

        $totalVeiculos  = Veiculo::count();
        $limiteVeiculos = $request->user()->empresa?->limite_veiculos;

        return view('veiculos.index', compact('veiculos', 'busca', 'totalVeiculos', 'limiteVeiculos'));
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
            'chassi'       => 'nullable|string|max:30',
            'validade_documento' => 'nullable|date',
            'capacidade_kg'=> 'nullable|numeric|min:0',
            'status'       => 'required|in:ativo,inativo,manutencao',
        ]);

        $empresa = $request->user()->empresa;

        if ($empresa && $empresa->limiteVeiculosAtingido()) {
            return back()->withErrors([
                'placa' => "Limite de {$empresa->limite_veiculos} veículo(s) do seu plano foi atingido. Fale com o suporte para ampliar.",
            ])->withInput();
        }

        Veiculo::create($request->all());

        return redirect()->route('veiculos.index')
            ->with('success', 'Veículo cadastrado com sucesso!');
    }

    public function show(Veiculo $veiculo)
    {
        $veiculo->load('criadoPor', 'atualizadoPor', 'manutencoes');
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
            'chassi'       => 'nullable|string|max:30',
            'validade_documento' => 'nullable|date',
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