<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tabelas_frete', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->restrictOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('origem');
            $table->string('origem_uf', 2);
            $table->string('origem_codigo_municipio', 7);
            $table->string('destino');
            $table->string('destino_uf', 2);
            $table->string('destino_codigo_municipio', 7);
            $table->decimal('valor_frete', 10, 2);
            $table->timestamps();

            $table->unique(
                ['cliente_id', 'origem_codigo_municipio', 'destino_codigo_municipio'],
                'tabelas_frete_cliente_rota_unica'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tabelas_frete');
    }
};
