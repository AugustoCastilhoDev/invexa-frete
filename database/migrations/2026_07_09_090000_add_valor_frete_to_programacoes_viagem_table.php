<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programacoes_viagem', function (Blueprint $table) {
            $table->decimal('valor_frete', 10, 2)->nullable()->after('destino');
        });
    }

    public function down(): void
    {
        Schema::table('programacoes_viagem', function (Blueprint $table) {
            $table->dropColumn('valor_frete');
        });
    }
};
