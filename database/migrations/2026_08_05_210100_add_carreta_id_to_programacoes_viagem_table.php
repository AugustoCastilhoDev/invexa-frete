<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programacoes_viagem', function (Blueprint $table) {
            // Carreta usada nesta viagem específica — independente do vínculo fixo
            // cavalo->carreta cadastrado em Veiculo::cavalo_id, pra dar pra trocar
            // pontualmente quando a carreta de sempre está em manutenção.
            $table->foreignId('carreta_id')->nullable()->after('veiculo_id')
                ->constrained('veiculos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('programacoes_viagem', function (Blueprint $table) {
            $table->dropConstrainedForeignId('carreta_id');
        });
    }
};
