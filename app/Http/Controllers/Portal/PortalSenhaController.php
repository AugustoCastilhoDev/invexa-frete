<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PortalSenhaController extends Controller
{
    public function edit()
    {
        return view('portal.senha.edit');
    }

    public function update(Request $request)
    {
        $request->validate([
            'senha_atual'          => 'required|string',
            'password'             => 'required|string|min:6|confirmed',
        ]);

        $motorista = Auth::guard('motorista')->user();

        if (! Hash::check($request->input('senha_atual'), $motorista->password)) {
            throw ValidationException::withMessages([
                'senha_atual' => 'Senha atual incorreta.',
            ]);
        }

        $motorista->forceFill([
            'password' => Hash::make($request->input('password')),
        ])->save();

        return redirect()->route('portal.senha.edit')
            ->with('success', 'Senha alterada com sucesso!');
    }
}
