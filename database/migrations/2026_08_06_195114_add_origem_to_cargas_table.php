<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Espelha o "destino" da carga (2026-08-06): se a viagem tem múltiplas
    // coletas em pontos diferentes, cada carga pode ter sua própria origem
    // também — nullable, com fallback pra origem da viagem quando vazio
    // (ver Carga::origem_efetivo).
    public function up(): void
    {
        Schema::table('cargas', function (Blueprint $table) {
            $table->string('origem')->nullable()->after('destino_codigo_municipio');
            $table->string('origem_uf', 2)->nullable()->after('origem');
            $table->string('origem_codigo_municipio', 7)->nullable()->after('origem_uf');
        });
    }

    public function down(): void
    {
        Schema::table('cargas', function (Blueprint $table) {
            $table->dropColumn(['origem', 'origem_uf', 'origem_codigo_municipio']);
        });
    }
};
