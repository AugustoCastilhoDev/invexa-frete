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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

    /**
     * Super admin passa a navegar autenticado como o admin da empresa, para
     * dar suporte vendo exatamente o que o cliente vê. A identidade original
     * fica guardada na sessão para poder voltar depois.
     */
    public function iniciarSuporte(Request $request, Empresa $empresa)
    {
        $superAdminId = $request->user()->id;

        $admin = User::where('empresa_id', $empresa->id)
            ->where('role', 'admin')
            ->where('status', 'ativo')
            ->orderBy('id')
            ->first();

        abort_unless($admin, 404, 'Esta empresa não tem nenhum administrador ativo para representar.');

        Auth::login($admin);
        $request->session()->regenerate();
        session([
            'suporte_super_admin_id' => $superAdminId,
            'suporte_empresa_nome'   => $empresa->nome,
        ]);

        Log::info("Suporte iniciado: super admin #{$superAdminId} acessando a empresa #{$empresa->id} ({$empresa->nome}) como {$admin->email}");

        return redirect()->route('dashboard')
            ->with('success', "Acessando como suporte em {$empresa->nome}.");
    }

    public function encerrarSuporte(Request $request)
    {
        $superAdminId = $request->session()->pull('suporte_super_admin_id');
        $request->session()->forget('suporte_empresa_nome');

        abort_unless($superAdminId, 403);

        Auth::loginUsingId($superAdminId);
        $request->session()->regenerate();

        Log::info("Suporte encerrado: voltando para o super admin #{$superAdminId}");

        return redirect()->route('empresas.index')
            ->with('success', 'Modo suporte encerrado.');
    }
}
