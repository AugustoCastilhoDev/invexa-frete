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
        Schema::table('emissoes_fiscais', function (Blueprint $table) {
            $table->timestamp('encerrado_em')->nullable();
            $table->string('protocolo_encerramento')->nullable();
            $table->json('payload_encerramento')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emissoes_fiscais', function (Blueprint $table) {
            $table->dropColumn(['encerrado_em', 'protocolo_encerramento', 'payload_encerramento']);
        });
    }
};
