<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * focus_nfe_token e focus_nfe_certificado_senha usam o cast 'encrypted'
     * do Laravel, cujo valor cifrado (JSON com iv/value/mac em base64)
     * facilmente passa dos 255 caracteres do VARCHAR original — causava
     * "Data too long for column" ao ativar uma empresa.
     */
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->text('focus_nfe_token')->nullable()->change();
            $table->text('focus_nfe_certificado_senha')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('focus_nfe_token')->nullable()->change();
            $table->string('focus_nfe_certificado_senha')->nullable()->change();
        });
    }
};
