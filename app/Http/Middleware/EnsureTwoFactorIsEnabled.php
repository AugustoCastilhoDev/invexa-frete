<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorIsEnabled
{
    /**
     * Handle an incoming request.
     *
     * Não se aplica durante o modo suporte (super admin autenticado como o
     * admin de uma empresa cliente, ver EmpresasController::iniciarSuporte):
     * 2FA é exigido de quem realmente possui a conta, não de quem está
     * temporariamente representando ela — bloquear suporte a clientes que
     * ainda não configuraram 2FA quebraria a própria ferramenta de suporte.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->has('suporte_super_admin_id')) {
            return $next($request);
        }

        $user = $request->user();

        if ($user?->requiresTwoFactorAuthentication() && ! $user->hasEnabledTwoFactorAuthentication()) {
            return redirect()->route('profile.edit')->with('aviso_2fa', true);
        }

        return $next($request);
    }
}
