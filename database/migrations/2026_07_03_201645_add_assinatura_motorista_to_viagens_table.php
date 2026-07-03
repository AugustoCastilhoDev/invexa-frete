<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('viagens', function (Blueprint $table) {
            $table->string('assinatura_motorista_path')->nullable()->after('observacoes');
            $table->timestamp('assinatura_motorista_em')->nullable()->after('assinatura_motorista_path');
        });
    }

    public function down(): void
    {
        Schema::table('viagens', function (Blueprint $table) {
            $table->dropColumn(['assinatura_motorista_path', 'assinatura_motorista_em']);
        });
    }
};
