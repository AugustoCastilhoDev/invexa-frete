<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Support\CsvImporter;
use Illuminate\Http\Request;

class ClientesController extends Controller
{
    public function index(Request $request)
    {
        $busca = $request->input('busca');

        $clientes = Cliente::when($busca, function ($query) use ($busca) {
                $query->where('nome', 'like', "%{$busca}%")
                    ->orWhere('razao_social', 'like', "%{$busca}%")
                    ->orWhere('cidade', 'like', "%{$busca}%")
                    ->orWhere('telefone', 'like', "%{$busca}%");

                // cpf_cnpj é cifrado (IV aleatório por gravação) — não dá pra
                // fazer LIKE nele; busca por documento passa a exigir o valor
                // completo (com ou sem pontuação), comparado pelo hash.
                if ($hash = Cliente::hashDocumento($busca)) {
                    $query->orWhere('cpf_cnpj_hash', $hash);
                }
            })
            ->orderBy('nome')
            ->paginate(15)
            ->withQueryString();

        return view('clientes.index', compact('clientes', 'busca'));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo_pessoa'  => 'required|in:fisica,juridica',
            'nome'         => 'required|string|max:255',
            'razao_social' => 'nullable|string|max:255',
            'cpf_cnpj'     => 'required|string|max:20',
            'ie'           => 'nullable|string|max:20',
            'email'        => 'nullable|email',
            'telefone'     => 'nullable|string|max:20',
            'celular'      => 'nullable|string|max:20',
            'contato'      => 'nullable|string|max:255',
            'cep'          => 'nullable|string|max:10',
            'logradouro'   => 'nullable|string|max:255',
            'numero'       => 'nullable|string|max:20',
            'complemento'  => 'nullable|string|max:255',
            'bairro'       => 'nullable|string|max:255',
            'cidade'       => 'nullable|string|max:255',
            'estado'       => 'nullable|string|max:2',
            'codigo_municipio' => 'nullable|string|max:7',
            'tabela_frete' => 'nullable|numeric|min:0',
            'observacoes'  => 'nullable|string',
            'status'       => 'required|in:ativo,inativo',
        ]);

        // cpf_cnpj é cifrado — a checagem de unicidade do Laravel (unique:clientes)
        // não funciona mais contra a coluna; passa a comparar pelo hash determinístico.
        if (Cliente::where('cpf_cnpj_hash', Cliente::hashDocumento($request->cpf_cnpj))->exists()) {
            return back()->withErrors(['cpf_cnpj' => 'Já existe um cliente cadastrado com este CPF/CNPJ.'])->withInput();
        }

        Cliente::create($request->all());

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente cadastrado com sucesso!');
    }

    public function show(Cliente $cliente)
    {
        $cliente->load('criadoPor', 'atualizadoPor');

        $viagens = $cliente->viagens()
            ->with(['motorista', 'veiculo'])
            ->orderByDesc('data_saida')
            ->paginate(10);

        return view('clientes.show', compact('cliente', 'viagens'));
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $request->validate([
            'tipo_pessoa'  => 'required|in:fisica,juridica',
            'nome'         => 'required|string|max:255',
            'razao_social' => 'nullable|string|max:255',
            'cpf_cnpj'     => 'required|string|max:20',
            'ie'           => 'nullable|string|max:20',
            'email'        => 'nullable|email',
            'telefone'     => 'nullable|string|max:20',
            'celular'      => 'nullable|string|max:20',
            'contato'      => 'nullable|string|max:255',
            'cep'          => 'nullable|string|max:10',
            'logradouro'   => 'nullable|string|max:255',
            'numero'       => 'nullable|string|max:20',
            'complemento'  => 'nullable|string|max:255',
            'bairro'       => 'nullable|string|max:255',
            'cidade'       => 'nullable|string|max:255',
            'estado'       => 'nullable|string|max:2',
            'codigo_municipio' => 'nullable|string|max:7',
            'tabela_frete' => 'nullable|numeric|min:0',
            'observacoes'  => 'nullable|string',
            'status'       => 'required|in:ativo,inativo',
        ]);

        if (Cliente::where('cpf_cnpj_hash', Cliente::hashDocumento($request->cpf_cnpj))
            ->where('id', '!=', $cliente->id)
            ->exists()) {
            return back()->withErrors(['cpf_cnpj' => 'Já existe um cliente cadastrado com este CPF/CNPJ.'])->withInput();
        }

        $cliente->update($request->all());

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente atualizado com sucesso!');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return redirect()->route('clientes.index')
            ->with('success', 'Cliente removido com sucesso!');
    }

    public function importar()
    {
        return view('clientes.importar');
    }

    public function importarTemplate()
    {
        $csv = "tipo_pessoa;nome;razao_social;cpf_cnpj;email;telefone;celular;cidade;estado;tabela_frete;status\n"
            . "juridica;Transportes Exemplo Ltda;Transportes Exemplo Ltda;12.345.678/0001-90;contato@exemplo.com;(11) 3333-4444;(11) 91234-5678;São Paulo;SP;1500;ativo\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="modelo-clientes.csv"',
        ]);
    }

    public function importarStore(Request $request)
    {
        $request->validate(['arquivo' => 'required|file|mimes:csv,txt']);

        $resultado = CsvImporter::importar(
            $request->file('arquivo'),
            [
                'tipo_pessoa' => 'tipo_pessoa',
                'nome' => 'nome',
                'razao_social' => 'razao_social',
                'cpf_cnpj' => 'cpf_cnpj',
                'email' => 'email',
                'telefone' => 'telefone',
                'celular' => 'celular',
                'cidade' => 'cidade',
                'estado' => 'estado',
                'tabela_frete' => 'tabela_frete',
                'status' => 'status',
            ],
            [
                'tipo_pessoa' => 'required|in:fisica,juridica',
                'nome' => 'required|string|max:255',
                'razao_social' => 'nullable|string|max:255',
                'cpf_cnpj' => 'required|string|max:20',
                'email' => 'nullable|email',
                'telefone' => 'nullable|string|max:20',
                'celular' => 'nullable|string|max:20',
                'cidade' => 'nullable|string|max:255',
                'estado' => 'nullable|string|max:2',
                'tabela_frete' => 'nullable|numeric|min:0',
                'status' => 'required|in:ativo,inativo',
            ],
            function (array $dados) {
                // cpf_cnpj é cifrado — a regra "unique" do Validator não
                // funciona mais contra a coluna; checagem manual pelo hash
                // (cobre tanto duplicata já no banco quanto duas linhas
                // repetidas dentro do mesmo CSV).
                if (Cliente::where('cpf_cnpj_hash', Cliente::hashDocumento($dados['cpf_cnpj']))->exists()) {
                    throw new \RuntimeException('Já existe um cliente cadastrado com este CPF/CNPJ.');
                }

                Cliente::create($dados);
            }
        );

        return redirect()->route('clientes.index')->with('importacao', $resultado);
    }
}