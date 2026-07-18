<?php

namespace App\Http\Controllers;

use App\Models\Motorista;
use App\Support\CsvImporter;
use Illuminate\Http\Request;

class MotoristasController extends Controller
{
    public function index(Request $request)
    {
    $busca = $request->input('busca');

    $motoristas = Motorista::when($busca, function ($query) use ($busca) {
            $query->where('nome', 'like', "%{$busca}%")
                  ->orWhere('telefone', 'like', "%{$busca}%");

            // cpf é cifrado (IV aleatório por gravação) — não dá pra fazer
            // LIKE nele; busca por CPF passa a exigir o valor completo (com
            // ou sem pontuação), comparado pelo hash.
            if ($hash = Motorista::hashDocumento($busca)) {
                $query->orWhere('cpf_hash', $hash);
            }
        })
        ->orderBy('nome')
        ->paginate(15)
        ->withQueryString();

    return view('motoristas.index', compact('motoristas', 'busca'));
    }

    public function create()
    {
        return view('motoristas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome'                => 'required|string|max:255',
            'cpf'                 => 'required|string|max:14',
            'cnh'                 => 'nullable|string|max:20',
            'categoria_cnh'       => 'nullable|string|max:5',
            'validade_cnh'        => 'nullable|date',
            'telefone'            => 'nullable|string|max:20',
            'email'               => 'nullable|email',
            'percentual_comissao' => 'required|numeric|min:0|max:100',
            'status'              => 'required|in:ativo,inativo',
        ]);

        // cpf é cifrado — a checagem de unicidade do Laravel (unique:motoristas)
        // não funciona mais contra a coluna; passa a comparar pelo hash determinístico.
        if (Motorista::where('cpf_hash', Motorista::hashDocumento($request->cpf))->exists()) {
            return back()->withErrors(['cpf' => 'Já existe um motorista cadastrado com este CPF.'])->withInput();
        }

        Motorista::create($request->all());

        return redirect()->route('motoristas.index')
            ->with('success', 'Motorista cadastrado com sucesso!');
    }

    public function show(Motorista $motorista)
    {
        $motorista->load('criadoPor', 'atualizadoPor');
        $viagens = $motorista->viagens()->orderByDesc('data_saida')->paginate(10);
        return view('motoristas.show', compact('motorista', 'viagens'));
    }

    public function edit(Motorista $motorista)
    {
        return view('motoristas.edit', compact('motorista'));
    }

    public function update(Request $request, Motorista $motorista)
    {
        $request->validate([
            'nome'                => 'required|string|max:255',
            'cpf'                 => 'required|string|max:14',
            'cnh'                 => 'nullable|string|max:20',
            'categoria_cnh'       => 'nullable|string|max:5',
            'validade_cnh'        => 'nullable|date',
            'telefone'            => 'nullable|string|max:20',
            'email'               => 'nullable|email',
            'percentual_comissao' => 'required|numeric|min:0|max:100',
            'status'              => 'required|in:ativo,inativo',
        ]);

        if (Motorista::where('cpf_hash', Motorista::hashDocumento($request->cpf))
            ->where('id', '!=', $motorista->id)
            ->exists()) {
            return back()->withErrors(['cpf' => 'Já existe um motorista cadastrado com este CPF.'])->withInput();
        }

        $motorista->update($request->all());

        return redirect()->route('motoristas.index')
            ->with('success', 'Motorista atualizado com sucesso!');
    }

    public function destroy(Motorista $motorista)
    {
        $motorista->delete();
        return redirect()->route('motoristas.index')
            ->with('success', 'Motorista removido com sucesso!');
    }

    public function importar()
    {
        return view('motoristas.importar');
    }

    public function importarTemplate()
    {
        $csv = "nome;cpf;cnh;categoria_cnh;validade_cnh;telefone;email;percentual_comissao;status\n"
            . "João da Silva;123.456.789-00;12345678900;AB;31/12/2028;(11) 91234-5678;joao@email.com;10;ativo\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="modelo-motoristas.csv"',
        ]);
    }

    public function importarStore(Request $request)
    {
        $request->validate(['arquivo' => 'required|file|mimes:csv,txt']);

        $resultado = CsvImporter::importar(
            $request->file('arquivo'),
            [
                'nome' => 'nome',
                'cpf' => 'cpf',
                'cnh' => 'cnh',
                'categoria_cnh' => 'categoria_cnh',
                'validade_cnh' => 'validade_cnh',
                'telefone' => 'telefone',
                'email' => 'email',
                'percentual_comissao' => 'percentual_comissao',
                'status' => 'status',
            ],
            [
                'nome' => 'required|string|max:255',
                'cpf' => 'required|string|max:14',
                'cnh' => 'nullable|string|max:20',
                'categoria_cnh' => 'nullable|string|max:5',
                'validade_cnh' => 'nullable|date',
                'telefone' => 'nullable|string|max:20',
                'email' => 'nullable|email',
                'percentual_comissao' => 'required|numeric|min:0|max:100',
                'status' => 'required|in:ativo,inativo',
            ],
            function (array $dados) {
                // cpf é cifrado — a regra "unique" do Validator não funciona
                // mais contra a coluna; checagem manual pelo hash (cobre
                // tanto duplicata já no banco quanto duas linhas repetidas
                // dentro do mesmo CSV).
                if (Motorista::where('cpf_hash', Motorista::hashDocumento($dados['cpf']))->exists()) {
                    throw new \RuntimeException('Já existe um motorista cadastrado com este CPF.');
                }

                Motorista::create($dados);
            }
        );

        return redirect()->route('motoristas.index')->with('importacao', $resultado);
    }
}