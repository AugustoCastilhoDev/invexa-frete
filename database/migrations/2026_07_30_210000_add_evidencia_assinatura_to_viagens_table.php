<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('viagens', function (Blueprint $table) {
            $table->string('assinatura_motorista_ip', 45)->nullable()->after('assinatura_motorista_em');
            $table->string('assinatura_motorista_user_agent', 512)->nullable()->after('assinatura_motorista_ip');
        });
    }

    public function down(): void
    {
        Schema::table('viagens', function (Blueprint $table) {
            $table->dropColumn(['assinatura_motorista_ip', 'assinatura_motorista_user_agent']);
        });
    }
};
