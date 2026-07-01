<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('viagens', function (Blueprint $table) {
            // Adiciona cliente_id após veiculo_id
            $table->foreignId('cliente_id')
                  ->nullable()
                  ->after('veiculo_id')
                  ->constrained('clientes')
                  ->nullOnDelete();

            // Remove o campo texto antigo
            $table->dropColumn('cliente');
        });
    }

    public function down(): void
    {
        Schema::table('viagens', function (Blueprint $table) {
            $table->dropForeign(['cliente_id']);
            $table->dropColumn('cliente_id');
            $table->string('cliente')->nullable()->after('veiculo_id');
        });
    }
};