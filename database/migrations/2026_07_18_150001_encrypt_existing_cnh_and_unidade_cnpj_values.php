<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Backfill: cifra os valores já existentes em motoristas.cnh e
     * unidades.cnpj antes do model passar a usar o cast 'encrypted' —
     * sem isso, o primeiro read via Eloquent quebraria tentando
     * descriptografar texto puro (DecryptException).
     */
    public function up(): void
    {
        DB::table('motoristas')
            ->whereNotNull('cnh')
            ->where('cnh', '!=', '')
            ->orderBy('id')
            ->each(function (object $motorista) {
                DB::table('motoristas')
                    ->where('id', $motorista->id)
                    ->update(['cnh' => Crypt::encryptString($motorista->cnh)]);
            });

        DB::table('unidades')
            ->whereNotNull('cnpj')
            ->where('cnpj', '!=', '')
            ->orderBy('id')
            ->each(function (object $unidade) {
                DB::table('unidades')
                    ->where('id', $unidade->id)
                    ->update(['cnpj' => Crypt::encryptString($unidade->cnpj)]);
            });
    }

    public function down(): void
    {
        DB::table('motoristas')
            ->whereNotNull('cnh')
            ->where('cnh', '!=', '')
            ->orderBy('id')
            ->each(function (object $motorista) {
                try {
                    $plano = Crypt::decryptString($motorista->cnh);
                } catch (\Illuminate\Contracts\Encryption\DecryptException) {
                    return; // já estava em texto puro (ex.: rollback repetido)
                }

                DB::table('motoristas')->where('id', $motorista->id)->update(['cnh' => $plano]);
            });

        DB::table('unidades')
            ->whereNotNull('cnpj')
            ->where('cnpj', '!=', '')
            ->orderBy('id')
            ->each(function (object $unidade) {
                try {
                    $plano = Crypt::decryptString($unidade->cnpj);
                } catch (\Illuminate\Contracts\Encryption\DecryptException) {
                    return;
                }

                DB::table('unidades')->where('id', $unidade->id)->update(['cnpj' => $plano]);
            });
    }
};
