<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('viagens', function (Blueprint $table) {
            $table->string('assinatura_motorista_comprovante_path')->nullable()->after('assinatura_motorista_user_agent');
            $table->json('assinaturas_motorista_historico')->nullable()->after('assinatura_motorista_comprovante_path');
        });
    }

    public function down(): void
    {
        Schema::table('viagens', function (Blueprint $table) {
            $table->dropColumn(['assinatura_motorista_comprovante_path', 'assinaturas_motorista_historico']);
        });
    }
};
