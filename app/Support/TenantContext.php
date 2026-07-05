<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

class TenantContext
{
    private static ?int $forcedId = null;

    /**
     * Reentrância: resolver o guard 'motorista' consulta o model Motorista,
     * que tem este mesmo escopo global — sem essa trava, a primeira chamada
     * (ainda resolvendo o usuário da sessão) dispara outra igual e entra em
     * loop infinito até estourar a memória. Enquanto já está resolvendo,
     * devolve null (sem filtro) e deixa a consulta original completar.
     */
    private static bool $resolving = false;

    /**
     * Empresa do usuário autenticado no momento (guard web ou motorista).
     * Sem ninguém autenticado (comandos artisan, seeders, login antes de
     * autenticar), retorna null — o que faz o escopo global não filtrar nada.
     */
    public static function id(): ?int
    {
        if (static::$resolving) {
            return null;
        }

        static::$resolving = true;

        try {
            if (Auth::guard('web')->check()) {
                return Auth::guard('web')->user()->empresa_id;
            }

            if (Auth::guard('motorista')->check()) {
                return Auth::guard('motorista')->user()->empresa_id;
            }

            return static::$forcedId;
        } finally {
            static::$resolving = false;
        }
    }

    /**
     * Só para testes: simula um tenant corrente quando não há autenticação.
     */
    public static function forceId(?int $id): void
    {
        static::$forcedId = $id;
    }
}
