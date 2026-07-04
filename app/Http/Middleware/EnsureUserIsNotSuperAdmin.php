<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsNotSuperAdmin
{
    /**
     * O super admin não pertence a nenhuma empresa, então as telas
     * operacionais (escopadas por empresa) não fazem sentido pra ele.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isSuperAdmin()) {
            return redirect()->route('empresas.index');
        }

        return $next($request);
    }
}
