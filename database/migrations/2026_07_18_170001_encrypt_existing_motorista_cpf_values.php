<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Backfill: cifra motoristas.cpf e preenche o hash determinístico antes
     * do model passar a usar o cast 'encrypted' — sem isso, o primeiro read
     * via Eloquent (inclusive o login do Portal do Motorista) quebraria
     * tentando descriptografar texto puro (DecryptException).
     */
    public function up(): void
    {
        DB::transaction(function () {
            $this->cifrarEHashear();
        });
    }

    /**
     * Antes de gravar, confere se dois motoristas não normalizam pro mesmo
     * CPF (formatação diferente do mesmo número) — nesse caso a migration
     * para com uma mensagem clara em vez de estourar erro de índice único
     * no meio do loop.
     */
    private function cifrarEHashear(): void
    {
        $vistos = [];

        DB::table('motoristas')
            ->whereNotNull('cpf')
            ->where('cpf', '!=', '')
            ->orderBy('id')
            ->each(function (object $motorista) use (&$vistos) {
                $normalizado = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $motorista->cpf));
                $hash = hash_hmac('sha256', $normalizado, config('app.key'));

                if (isset($vistos[$hash])) {
                    throw new \RuntimeException(
                        "Migration abortada: motoristas #{$vistos[$hash]} e #{$motorista->id} têm o mesmo CPF normalizado ('{$normalizado}'), só formatado diferente. Resolva manualmente (duplicata real ou dado inconsistente) antes de rodar esta migration."
                    );
                }
                $vistos[$hash] = $motorista->id;

                DB::table('motoristas')->where('id', $motorista->id)->update([
                    'cpf' => Crypt::encryptString($motorista->cpf),
                    'cpf_hash' => $hash,
                ]);
            });
    }

    public function down(): void
    {
        DB::transaction(function () {
            DB::table('motoristas')
                ->whereNotNull('cpf')
                ->where('cpf', '!=', '')
                ->orderBy('id')
                ->each(function (object $motorista) {
                    try {
                        $plano = Crypt::decryptString($motorista->cpf);
                    } catch (\Illuminate\Contracts\Encryption\DecryptException) {
                        return; // já estava em texto puro (ex.: rollback repetido)
                    }

                    DB::table('motoristas')->where('id', $motorista->id)->update(['cpf' => $plano, 'cpf_hash' => null]);
                });
        });
    }
};
