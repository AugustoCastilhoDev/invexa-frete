<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\User;
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
