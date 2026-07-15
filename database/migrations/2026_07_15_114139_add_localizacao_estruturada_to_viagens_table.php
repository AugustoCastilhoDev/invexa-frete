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
        Schema::table('viagens', function (Blueprint $table) {
            $table->string('origem_uf', 2)->nullable();
            $table->string('origem_codigo_municipio', 7)->nullable();
            $table->string('destino_uf', 2)->nullable();
            $table->string('destino_codigo_municipio', 7)->nullable();
            $table->string('descricao_carga')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('viagens', function (Blueprint $table) {
            $table->dropColumn([
                'origem_uf', 'origem_codigo_municipio',
                'destino_uf', 'destino_codigo_municipio', 'descricao_carga',
            ]);
        });
    }
};
