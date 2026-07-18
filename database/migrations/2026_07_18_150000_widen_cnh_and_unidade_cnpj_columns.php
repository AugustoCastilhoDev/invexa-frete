<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Prepara motoristas.cnh e unidades.cnpj para o cast 'encrypted' do
     * Laravel: o valor cifrado (JSON com iv/value/mac em base64) passa
     * facilmente do VARCHAR(20) original — mesmo problema já visto em
     * empresas.focus_nfe_token (ver migration 2026_07_09_190000).
     */
    public function up(): void
    {
        Schema::table('motoristas', function (Blueprint $table) {
            $table->text('cnh')->nullable()->change();
        });

        Schema::table('unidades', function (Blueprint $table) {
            $table->text('cnpj')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('motoristas', function (Blueprint $table) {
            $table->string('cnh', 20)->nullable()->change();
        });

        Schema::table('unidades', function (Blueprint $table) {
            $table->string('cnpj', 20)->nullable()->change();
        });
    }
};
