<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('cep')->nullable();
            $table->string('logradouro')->nullable();
            $table->string('numero')->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('municipio')->nullable();
            $table->string('codigo_municipio', 7)->nullable();
            $table->string('uf', 2)->nullable();
            $table->string('telefone')->nullable();
            $table->string('inscricao_estadual')->nullable();
            $table->string('rntrc')->nullable();
            $table->string('regime_tributario')->nullable();
            $table->string('cfop_padrao')->nullable();
            $table->string('icms_situacao_tributaria')->nullable();
            $table->decimal('icms_aliquota', 5, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn([
                'cep', 'logradouro', 'numero', 'complemento', 'bairro',
                'municipio', 'codigo_municipio', 'uf', 'telefone',
                'inscricao_estadual', 'rntrc', 'regime_tributario',
                'cfop_padrao', 'icms_situacao_tributaria', 'icms_aliquota',
            ]);
        });
    }
};
