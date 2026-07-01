<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClientesController extends Controller
{
    public function index(Request $request)
    {
        $busca = $request->input('busca');

        $clientes = Cliente::when($busca, function ($query) use ($busca) {
                $query->where('nome', 'like', "%{$busca}%")
                    ->orWhere('razao_social', 'like', "%{$busca}%")
                    ->orWhere('cpf_cnpj', 'like', "%{$busca}%")
                    ->orWhere('cidade', 'like', "%{$busca}%")
                    ->orWhere('telefone', 'like', "%{$busca}%");
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
            'cpf_cnpj'     => 'required|string|max:20|unique:clientes',
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
            'tabela_frete' => 'nullable|numeric|min:0',
            'observacoes'  => 'nullable|string',
            'status'       => 'required|in:ativo,inativo',
        ]);

        Cliente::create($request->all());

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente cadastrado com sucesso!');
    }

    public function show(Cliente $cliente)
    {
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
            'cpf_cnpj'     => 'required|string|max:20|unique:clientes,cpf_cnpj,' . $cliente->id,
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
            'tabela_frete' => 'nullable|numeric|min:0',
            'observacoes'  => 'nullable|string',
            'status'       => 'required|in:ativo,inativo',
        ]);

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
}