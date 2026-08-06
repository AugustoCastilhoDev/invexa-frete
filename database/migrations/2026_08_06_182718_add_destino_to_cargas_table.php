<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Nullable de propósito: quando vazio, a Carga usa o destino da própria
    // Viagem (comportamento de hoje) — só preenche quando essa carga
    // específica entrega em cidade diferente das demais da mesma viagem
    // (fracionado com destinos distintos).
    public function up(): void
    {
        Schema::table('cargas', function (Blueprint $table) {
            $table->string('destino')->nullable()->after('cliente_id');
            $table->string('destino_uf', 2)->nullable()->after('destino');
            $table->string('destino_codigo_municipio', 7)->nullable()->after('destino_uf');
        });
    }

    public function down(): void
    {
        Schema::table('cargas', function (Blueprint $table) {
            $table->dropColumn(['destino', 'destino_uf', 'destino_codigo_municipio']);
        });
    }
};
