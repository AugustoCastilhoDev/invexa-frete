<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programacoes_viagem', function (Blueprint $table) {
            $table->time('hora_coleta')->nullable()->after('data_prevista');
            $table->date('data_entrega_prevista')->nullable()->after('hora_coleta');
            $table->time('hora_entrega_prevista')->nullable()->after('data_entrega_prevista');
            // Horário que o motorista efetivamente informa ter chegado no local de
            // coleta — pode divergir de chegada_informada_em se ele só conseguir
            // enviar depois (sem sinal no local). O cálculo de risco de no-show usa
            // sempre este campo, nunca o momento em que o registro chegou ao sistema.
            $table->dateTime('chegada_horario_informado')->nullable()->after('hora_entrega_prevista');
            $table->dateTime('chegada_informada_em')->nullable()->after('chegada_horario_informado');
        });
    }

    public function down(): void
    {
        Schema::table('programacoes_viagem', function (Blueprint $table) {
            $table->dropColumn([
                'hora_coleta',
                'data_entrega_prevista',
                'hora_entrega_prevista',
                'chegada_horario_informado',
                'chegada_informada_em',
            ]);
        });
    }
};
