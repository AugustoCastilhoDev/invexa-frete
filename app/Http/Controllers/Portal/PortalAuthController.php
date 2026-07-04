<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\PortalLoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalAuthController extends Controller
{
    public function create()
    {
        if (Auth::guard('motorista')->check()) {
            return redirect()->route('portal.viagens.index');
        }

        return view('portal.auth.login');
    }

    public function store(PortalLoginRequest $request)
    {
        $request->authenticate();

        // A chave de sessão "url.intended" é compartilhada entre guards (web e motorista).
        // Só reaproveitamos o destino salvo se ele realmente for uma rota do portal —
        // caso contrário, ele pode ter sido gravado por uma tentativa de acesso ao
        // painel admin em outra aba/sessão, o que mandaria o motorista para lá.
        $intendido = $request->session()->get('url.intended');

        if ($intendido && ! str_contains($intendido, '/portal')) {
            $request->session()->forget('url.intended');
        }

        return redirect()->intended(route('portal.viagens.index'));
    }

    public function destroy(Request $request)
    {
        Auth::guard('motorista')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login');
    }
}
