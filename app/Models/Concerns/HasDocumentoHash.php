<?php

namespace App\Models\Concerns;

trait HasDocumentoHash
{
    /**
     * Hash determinístico (HMAC-SHA256, chaveado pela APP_KEY) do documento
     * normalizado — usado para unicidade e busca exata sobre a coluna
     * cifrada, já que o cast 'encrypted' produz um valor diferente a cada
     * gravação (IV aleatório) e não dá pra comparar/buscar direto em SQL.
     */
    public static function hashDocumento(?string $valor): ?string
    {
        $normalizado = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $valor));

        if ($normalizado === '') {
            return null;
        }

        return hash_hmac('sha256', $normalizado, config('app.key'));
    }
}
