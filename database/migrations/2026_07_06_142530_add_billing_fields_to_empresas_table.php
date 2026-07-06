<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->enum('plano', ['starter', 'pro', 'business', 'enterprise'])->nullable()->after('limite_veiculos');
            $table->enum('ciclo_cobranca', ['mensal', 'anual'])->nullable()->after('plano');
            $table->string('asaas_customer_id')->nullable()->after('ciclo_cobranca');
            $table->string('asaas_subscription_id')->nullable()->after('asaas_customer_id');
            $table->string('asaas_status')->nullable()->after('asaas_subscription_id');
            $table->timestamp('asaas_last_event_at')->nullable()->after('asaas_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn([
                'plano',
                'ciclo_cobranca',
                'asaas_customer_id',
                'asaas_subscription_id',
                'asaas_status',
                'asaas_last_event_at',
            ]);
        });
    }
};
