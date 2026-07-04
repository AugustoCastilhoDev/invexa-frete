<?php

namespace App\Http\Controllers;

use App\Models\Motorista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MotoristaPortalAccessController extends Controller
{
    public function store(Request $request, Motorista $motorista)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $motorista->forceFill([
            'password'     => Hash::make($request->input('password')),
            'portal_ativo' => true,
        ])->save();

        return redirect()->route('motoristas.edit', $motorista)
            ->with('success', 'Acesso ao portal ativado e senha definida com sucesso!');
    }

    public function destroy(Motorista $motorista)
    {
        $motorista->forceFill(['portal_ativo' => false])->save();

        return redirect()->route('motoristas.edit', $motorista)
            ->with('success', 'Acesso ao portal desativado.');
    }
}
