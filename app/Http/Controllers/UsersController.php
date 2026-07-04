<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $busca = $request->input('busca');

        $users = User::where('empresa_id', $request->user()->empresa_id)
            ->when($busca, function ($query) use ($busca) {
                $query->where('name', 'like', "%{$busca}%")
                    ->orWhere('email', 'like', "%{$busca}%");
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', compact('users', 'busca'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role'     => 'required|in:admin,operador',
            'status'   => 'required|in:ativo,inativo',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password,
            'role'     => $request->role,
            'status'   => $request->status,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Usuário cadastrado com sucesso!');
    }

    public function edit(Request $request, User $user)
    {
        abort_unless($user->empresa_id === $request->user()->empresa_id, 404);

        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        abort_unless($user->empresa_id === $request->user()->empresa_id, 404);

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role'     => 'required|in:admin,operador',
            'status'   => 'required|in:ativo,inativo',
        ]);

        if ($user->id === $request->user()->id && $request->role !== 'admin') {
            return back()->withErrors(['role' => 'Você não pode remover seu próprio acesso de administrador.'])->withInput();
        }

        if ($this->seriaUltimoAdminAtivo($user, $request->role, $request->status)) {
            return back()->withErrors(['role' => 'Deve haver ao menos um administrador ativo no sistema.'])->withInput();
        }

        $user->fill([
            'name'   => $request->name,
            'email'  => $request->email,
            'role'   => $request->role,
            'status' => $request->status,
        ]);

        if ($request->filled('password')) {
            $user->password = $request->password;
        }

        $user->save();

        return redirect()->route('users.index')
            ->with('success', 'Usuário atualizado com sucesso!');
    }

    public function destroy(Request $request, User $user)
    {
        abort_unless($user->empresa_id === $request->user()->empresa_id, 404);

        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Você não pode desativar seu próprio usuário.');
        }

        if ($this->seriaUltimoAdminAtivo($user, 'operador', 'inativo')) {
            return back()->with('error', 'Deve haver ao menos um administrador ativo no sistema.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Usuário desativado com sucesso!');
    }

    private function seriaUltimoAdminAtivo(User $user, string $novoRole, string $novoStatus): bool
    {
        if ($user->role !== 'admin' || $user->status !== 'ativo') {
            return false;
        }

        if ($novoRole === 'admin' && $novoStatus === 'ativo') {
            return false;
        }

        return User::where('empresa_id', $user->empresa_id)
            ->where('role', 'admin')
            ->where('status', 'ativo')
            ->count() <= 1;
    }
}
