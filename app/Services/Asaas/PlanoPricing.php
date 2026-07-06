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
            'starter' => ['limite_veiculos' => 5, 'mensal' => 590.00, 'anual' => 5900.00],
            'pro' => ['limite_veiculos' => 15, 'mensal' => 1290.00, 'anual' => 12900.00],
            'business' => ['limite_veiculos' => 30, 'mensal' => 2190.00, 'anual' => 21900.00],
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
