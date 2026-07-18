<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Backfill: cifra clientes.cpf_cnpj e empresas.cnpj e preenche o hash
     * determinístico correspondente, antes do model passar a usar o cast
     * 'encrypted' — sem isso, o primeiro read via Eloquent quebraria
     * tentando descriptografar texto puro (DecryptException).
     */
    public function up(): void
    {
        DB::transaction(function () {
            $this->cifrarEHashear('clientes', 'cpf_cnpj', 'cpf_cnpj_hash');
            $this->cifrarEHashear('empresas', 'cnpj', 'cnpj_hash');
        });
    }

    /**
     * Antes de gravar, confere se duas linhas não normalizam pro mesmo
     * documento (ex.: "12345678000190" e "12.345.678/0001-90" cadastrados
     * como "diferentes" pelo índice único antigo por serem strings
     * literalmente distintas, mas na prática o mesmo CNPJ) — nesse caso a
     * migration para com uma mensagem clara em vez de estourar um erro de
     * índice único no meio do loop.
     */
    private function cifrarEHashear(string $tabela, string $coluna, string $colunaHash): void
    {
        $vistos = [];

        DB::table($tabela)
            ->whereNotNull($coluna)
            ->where($coluna, '!=', '')
            ->orderBy('id')
            ->each(function (object $linha) use ($tabela, $coluna, $colunaHash, &$vistos) {
                $normalizado = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $linha->$coluna));
                $hash = hash_hmac('sha256', $normalizado, config('app.key'));

                if (isset($vistos[$hash])) {
                    throw new \RuntimeException(
                        "Migration abortada: registros #{$vistos[$hash]} e #{$linha->id} da tabela {$tabela} têm o mesmo documento normalizado ('{$normalizado}'), só formatado diferente. Resolva manualmente (duplicata real ou dado inconsistente) antes de rodar esta migration."
                    );
                }
                $vistos[$hash] = $linha->id;

                DB::table($tabela)->where('id', $linha->id)->update([
                    $coluna => Crypt::encryptString($linha->$coluna),
                    $colunaHash => $hash,
                ]);
            });
    }

    public function down(): void
    {
        DB::transaction(function () {
            $this->reverter('clientes', 'cpf_cnpj', 'cpf_cnpj_hash');
            $this->reverter('empresas', 'cnpj', 'cnpj_hash');
        });
    }

    private function reverter(string $tabela, string $coluna, string $colunaHash): void
    {
        DB::table($tabela)
            ->whereNotNull($coluna)
            ->where($coluna, '!=', '')
            ->orderBy('id')
            ->each(function (object $linha) use ($tabela, $coluna, $colunaHash) {
                try {
                    $plano = Crypt::decryptString($linha->$coluna);
                } catch (\Illuminate\Contracts\Encryption\DecryptException) {
                    return; // já estava em texto puro (ex.: rollback repetido)
                }

                DB::table($tabela)->where('id', $linha->id)->update([$coluna => $plano, $colunaHash => null]);
            });
    }
};
