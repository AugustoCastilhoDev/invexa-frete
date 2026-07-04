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
