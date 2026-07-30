<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programacoes_viagem', function (Blueprint $table) {
            $table->string('origem_uf', 2)->nullable()->after('origem');
            $table->string('origem_codigo_municipio', 7)->nullable()->after('origem_uf');
            $table->string('destino_uf', 2)->nullable()->after('destino');
            $table->string('destino_codigo_municipio', 7)->nullable()->after('destino_uf');
        });
    }

    public function down(): void
    {
        Schema::table('programacoes_viagem', function (Blueprint $table) {
            $table->dropColumn(['origem_uf', 'origem_codigo_municipio', 'destino_uf', 'destino_codigo_municipio']);
        });
    }
};
