<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Unifica "destino adicional" (só entrega) num conceito mais amplo de
    // "parada" (coleta OU entrega) — preparação pra roteirização de verdade
    // no futuro, que precisa da sequência real de todas as paradas juntas,
    // não só das entregas. Sem uso real em produção ainda (feature nova,
    // ainda não tinha cliente usando), então renomear agora é seguro —
    // nenhum dado real fica pra trás.
    public function up(): void
    {
        Schema::rename('destinos_programacao', 'paradas_programacao');

        Schema::table('paradas_programacao', function (Blueprint $table) {
            $table->enum('tipo', ['coleta', 'entrega'])->default('entrega')->after('programacao_viagem_id');
        });
    }

    public function down(): void
    {
        Schema::table('paradas_programacao', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });

        Schema::rename('paradas_programacao', 'destinos_programacao');
    }
};
