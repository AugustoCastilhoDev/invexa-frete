<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

class TenantContext
{
    private static ?int $forcedId = null;

    /**
     * Empresa do usuário autenticado no momento (guard web ou motorista).
     * Sem ninguém autenticado (comandos artisan, seeders, login antes de
     * autenticar), retorna null — o que faz o escopo global não filtrar nada.
     */
    public static function id(): ?int
    {
        if (Auth::guard('web')->check()) {
            return Auth::guard('web')->user()->empresa_id;
        }

        if (Auth::guard('motorista')->check()) {
            return Auth::guard('motorista')->user()->empresa_id;
        }

        return static::$forcedId;
    }

    /**
     * Só para testes: simula um tenant corrente quando não há autenticação.
     */
    public static function forceId(?int $id): void
    {
        static::$forcedId = $id;
    }
}
