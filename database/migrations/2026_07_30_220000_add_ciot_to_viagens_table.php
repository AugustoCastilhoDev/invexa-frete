<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('viagens', function (Blueprint $table) {
            $table->string('ciot', 12)->nullable()->after('assinatura_motorista_user_agent');
            $table->string('ciot_cpf_responsavel', 11)->nullable()->after('ciot');
            $table->string('ciot_cnpj_responsavel', 14)->nullable()->after('ciot_cpf_responsavel');
        });
    }

    public function down(): void
    {
        Schema::table('viagens', function (Blueprint $table) {
            $table->dropColumn(['ciot', 'ciot_cpf_responsavel', 'ciot_cnpj_responsavel']);
        });
    }
};
