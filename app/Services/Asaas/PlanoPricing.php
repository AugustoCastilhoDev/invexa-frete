<?php

namespace App\Services\Asaas;

class PlanoPricing
{
    /**
     * Tabela de planos: limite de veículos e valor por ciclo de cobrança.
     * Enterprise é sempre negociado à parte — não gera assinatura automática.
     */
    public static function tabela(): array
    {
        return [
            'starter' => ['limite_veiculos' => 5, 'mensal' => 639.00, 'anual' => 6390.00],
            'pro' => ['limite_veiculos' => 15, 'mensal' => 1339.00, 'anual' => 13390.00],
            'business' => ['limite_veiculos' => 30, 'mensal' => 2249.00, 'anual' => 22490.00],
            'enterprise' => ['limite_veiculos' => null, 'mensal' => null, 'anual' => null],
        ];
    }

    public static function limiteVeiculos(string $plano): ?int
    {
        return self::tabela()[$plano]['limite_veiculos'] ?? null;
    }

    public static function valor(string $plano, string $ciclo): ?float
    {
        return self::tabela()[$plano][$ciclo] ?? null;
    }
}
