<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Prepara clientes.cpf_cnpj e empresas.cnpj para o cast 'encrypted':
     * alarga as colunas (mesmo motivo das migrations anteriores de CNH/CNPJ
     * de unidade) e adiciona uma coluna de hash determinístico — como o
     * valor cifrado muda a cada gravação (IV aleatório), a unicidade e a
     * busca exata passam a depender do hash, não mais da coluna cifrada.
     */
    public function up(): void
    {
        // O índice único precisa sair ANTES de alargar pra TEXT — o MySQL
        // não aceita um índice único sobre BLOB/TEXT sem tamanho de chave
        // explícito ("key length"), então manter o índice durante o
        // ->change() quebra o ALTER TABLE.
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropUnique('clientes_cpf_cnpj_unique');
        });

        Schema::table('empresas', function (Blueprint $table) {
            $table->dropUnique('empresas_cnpj_unique');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->text('cpf_cnpj')->change();
            $table->string('cpf_cnpj_hash', 64)->nullable()->after('cpf_cnpj');
        });

        Schema::table('empresas', function (Blueprint $table) {
            $table->text('cnpj')->nullable()->change();
            $table->string('cnpj_hash', 64)->nullable()->after('cnpj');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->unique('cpf_cnpj_hash');
        });

        Schema::table('empresas', function (Blueprint $table) {
            $table->unique('cnpj_hash');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropUnique(['cpf_cnpj_hash']);
            $table->dropColumn('cpf_cnpj_hash');
        });

        Schema::table('empresas', function (Blueprint $table) {
            $table->dropUnique(['cnpj_hash']);
            $table->dropColumn('cnpj_hash');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->string('cpf_cnpj', 20)->change();
        });

        Schema::table('empresas', function (Blueprint $table) {
            $table->string('cnpj')->nullable()->change();
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->unique('cpf_cnpj');
        });

        Schema::table('empresas', function (Blueprint $table) {
            $table->unique('cnpj');
        });
    }
};
