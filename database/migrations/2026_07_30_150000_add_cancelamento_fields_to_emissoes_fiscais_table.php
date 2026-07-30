<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emissoes_fiscais', function (Blueprint $table) {
            $table->text('justificativa_cancelamento')->nullable();
            $table->timestamp('cancelado_em')->nullable();
            $table->string('protocolo_cancelamento')->nullable();
            $table->json('payload_cancelamento')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('emissoes_fiscais', function (Blueprint $table) {
            $table->dropColumn(['justificativa_cancelamento', 'cancelado_em', 'protocolo_cancelamento', 'payload_cancelamento']);
        });
    }
};
