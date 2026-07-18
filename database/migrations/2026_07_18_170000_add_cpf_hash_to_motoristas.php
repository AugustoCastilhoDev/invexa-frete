<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Prepara motoristas.cpf para o cast 'encrypted': alarga a coluna e
     * adiciona um hash determinístico — como o valor cifrado muda a cada
     * gravação (IV aleatório), a unicidade, a busca exata E o login do
     * Portal do Motorista (que hoje compara cpf via whereRaw) passam a
     * depender do hash, não mais da coluna cifrada.
     */
    public function up(): void
    {
        // Índice único precisa sair ANTES de alargar pra TEXT — o MySQL não
        // aceita índice único sobre BLOB/TEXT sem tamanho de chave explícito.
        Schema::table('motoristas', function (Blueprint $table) {
            $table->dropUnique('motoristas_cpf_unique');
        });

        Schema::table('motoristas', function (Blueprint $table) {
            $table->text('cpf')->change();
            $table->string('cpf_hash', 64)->nullable()->after('cpf');
        });

        Schema::table('motoristas', function (Blueprint $table) {
            $table->unique('cpf_hash');
        });
    }

    public function down(): void
    {
        Schema::table('motoristas', function (Blueprint $table) {
            $table->dropUnique(['cpf_hash']);
            $table->dropColumn('cpf_hash');
        });

        Schema::table('motoristas', function (Blueprint $table) {
            $table->string('cpf', 14)->change();
        });

        Schema::table('motoristas', function (Blueprint $table) {
            $table->unique('cpf');
        });
    }
};
