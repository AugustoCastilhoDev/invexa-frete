<?php

namespace App\Http\Controllers;

use App\Models\Veiculo;
use App\Support\CsvImporter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

        $totalVeiculos  = Veiculo::contamParaLimite()->count();
        $limiteVeiculos = $request->user()->empresa?->limite_veiculos;

        return view('veiculos.index', compact('veiculos', 'busca', 'totalVeiculos', 'limiteVeiculos'));
    }

    public function create()
    {
        $cavalos = Veiculo::where('tipo', 'truck')->orderBy('placa')->get();

        return view('veiculos.create', compact('cavalos'));
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
            'cavalo_id'    => ['nullable', 'prohibited_unless:tipo,carreta', Rule::exists('veiculos', 'id')->where('tipo', 'truck')],
            'capacidade_kg'=> 'nullable|numeric|min:0',
            'tara_kg'      => 'nullable|integer|min:0',
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
        $veiculo->load('criadoPor', 'atualizadoPor', 'manutencoes', 'cavalo', 'carretas');
        $viagens = $veiculo->viagens()->orderByDesc('data_saida')->paginate(10);
        return view('veiculos.show', compact('veiculo', 'viagens'));
    }

    public function edit(Veiculo $veiculo)
    {
        $cavalos = Veiculo::where('tipo', 'truck')->orderBy('placa')->get();

        return view('veiculos.edit', compact('veiculo', 'cavalos'));
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
            'cavalo_id'    => ['nullable', 'prohibited_unless:tipo,carreta', Rule::exists('veiculos', 'id')->where('tipo', 'truck')],
            'capacidade_kg'=> 'nullable|numeric|min:0',
            'tara_kg'      => 'nullable|integer|min:0',
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

    public function importar()
    {
        return view('veiculos.importar');
    }

    public function importarTemplate()
    {
        $csv = "placa;modelo;marca;ano;tipo;renavam;chassi;validade_documento;capacidade_kg;status\n"
            . "ABC1D23;FH 540;Volvo;2020;truck;12345678901;9BWZZZ377VT004251;31/03/2027;15000;ativo\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="modelo-veiculos.csv"',
        ]);
    }

    public function importarStore(Request $request)
    {
        $request->validate(['arquivo' => 'required|file|mimes:csv,txt']);

        $empresa = $request->user()->empresa;

        $resultado = CsvImporter::importar(
            $request->file('arquivo'),
            [
                'placa' => 'placa',
                'modelo' => 'modelo',
                'marca' => 'marca',
                'ano' => 'ano',
                'tipo' => 'tipo',
                'renavam' => 'renavam',
                'chassi' => 'chassi',
                'validade_documento' => 'validade_documento',
                'capacidade_kg' => 'capacidade_kg',
                'status' => 'status',
            ],
            [
                'placa' => 'required|string|max:10|unique:veiculos',
                'modelo' => 'required|string|max:255',
                'marca' => 'nullable|string|max:255',
                'ano' => 'nullable|integer|min:1990|max:' . (date('Y') + 1),
                'tipo' => 'required|in:truck,carreta,van,utilitario,outro',
                'renavam' => 'nullable|string|max:20',
                'chassi' => 'nullable|string|max:30',
                'validade_documento' => 'nullable|date',
                'capacidade_kg' => 'nullable|numeric|min:0',
                'status' => 'required|in:ativo,inativo,manutencao',
            ],
            function (array $dados) use ($empresa) {
                if ($empresa && $empresa->limiteVeiculosAtingido()) {
                    throw new \RuntimeException("Limite de {$empresa->limite_veiculos} veículo(s) do seu plano foi atingido.");
                }

                Veiculo::create($dados);
            }
        );

        return redirect()->route('veiculos.index')->with('importacao', $resultado);
    }
}