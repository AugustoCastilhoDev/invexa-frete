<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\DespesaGeral;
use App\Models\Empresa;
use App\Models\Motorista;
use App\Models\User;
use App\Models\Veiculo;
use App\Models\Viagem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class EmpresasController extends Controller
{
    public function index(Request $request)
    {
        $busca = $request->input('busca');

        $empresas = Empresa::when($busca, function ($query) use ($busca) {
                $query->where('nome', 'like', "%{$busca}%")
                    ->orWhere('cnpj', 'like', "%{$busca}%");
            })
            ->withCount('usuarios')
            ->orderBy('nome')
            ->paginate(15)
            ->withQueryString();

        return view('empresas.index', compact('empresas', 'busca'));
    }

    public function create()
    {
        return view('empresas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome'                 => 'required|string|max:255',
            'cnpj'                 => 'nullable|string|max:20|unique:empresas,cnpj',
            'admin_name'           => 'required|string|max:255',
            'admin_email'          => 'required|email|unique:users,email',
            'admin_password'       => ['required', 'confirmed', Password::defaults()],
        ]);

        $empresa = Empresa::create([
            'nome'   => $request->nome,
            'cnpj'   => $request->cnpj,
            'status' => 'ativo',
        ]);

        $admin = new User([
            'name'     => $request->admin_name,
            'email'    => $request->admin_email,
            'password' => $request->admin_password,
            'role'     => 'admin',
            'status'   => 'ativo',
        ]);
        $admin->empresa_id = $empresa->id;
        $admin->email_verified_at = now();
        $admin->save();

        return redirect()->route('empresas.index')
            ->with('success', 'Empresa cadastrada com sucesso! O administrador já pode fazer login.');
    }

    public function show(Empresa $empresa)
    {
        $empresa->load('criadoPor');

        $usuarios = $empresa->usuarios()->orderByDesc('role')->orderBy('name')->get();

        // Sem escopo global de propósito: aqui é o super admin olhando de fora,
        // não um usuário da própria empresa — precisa enxergar mesmo sem sessão scoped.
        $resumo = [
            'motoristas'        => Motorista::withoutGlobalScope('empresa')->where('empresa_id', $empresa->id)->count(),
            'motoristas_ativos' => Motorista::withoutGlobalScope('empresa')->where('empresa_id', $empresa->id)->where('status', 'ativo')->count(),
            'veiculos'          => Veiculo::withoutGlobalScope('empresa')->where('empresa_id', $empresa->id)->count(),
            'clientes'          => Cliente::withoutGlobalScope('empresa')->where('empresa_id', $empresa->id)->count(),
            'viagens'           => Viagem::withoutGlobalScope('empresa')->where('empresa_id', $empresa->id)->count(),
            'viagens_abertas'   => Viagem::withoutGlobalScope('empresa')->where('empresa_id', $empresa->id)
                ->whereIn('status', ['aberta', 'em_andamento', 'aguardando_acerto'])->count(),
            'despesas_gerais'   => DespesaGeral::withoutGlobalScope('empresa')->where('empresa_id', $empresa->id)->count(),
        ];

        return view('empresas.show', compact('empresa', 'usuarios', 'resumo'));
    }

    public function edit(Empresa $empresa)
    {
        return view('empresas.edit', compact('empresa'));
    }

    public function update(Request $request, Empresa $empresa)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'cnpj' => ['nullable', 'string', 'max:20', Rule::unique('empresas', 'cnpj')->ignore($empresa->id)],
        ]);

        $empresa->update($request->only('nome', 'cnpj'));

        return redirect()->route('empresas.index')
            ->with('success', 'Empresa atualizada com sucesso!');
    }

    public function toggleStatus(Empresa $empresa)
    {
        $empresa->update(['status' => $empresa->status === 'ativo' ? 'inativo' : 'ativo']);

        return redirect()->route('empresas.index')
            ->with('success', 'Status da empresa atualizado com sucesso!');
    }
}
