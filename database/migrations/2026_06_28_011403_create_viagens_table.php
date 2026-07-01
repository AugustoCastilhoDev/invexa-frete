<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('viagens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('motorista_id')->constrained('motoristas');
            $table->foreignId('veiculo_id')->constrained('veiculos');
            $table->string('origem');
            $table->string('destino');
            $table->string('cliente')->nullable();
            $table->date('data_saida');
            $table->date('data_retorno')->nullable();
            $table->integer('km_inicial')->nullable();
            $table->integer('km_final')->nullable();
            $table->decimal('valor_frete', 10, 2)->default(0);
            $table->decimal('percentual_motorista', 5, 2)->default(0); // % herdado do motorista
            $table->decimal('valor_motorista', 10, 2)->default(0);     // calculado automaticamente
            $table->decimal('total_combustivel', 10, 2)->default(0);   // soma dos lançamentos
            $table->decimal('total_manutencao', 10, 2)->default(0);    // soma dos lançamentos
            $table->decimal('total_descontos', 10, 2)->default(0);     // soma dos descontos
            $table->decimal('valor_adiantamento', 10, 2)->default(0);  // vale-viagem
            $table->decimal('saldo_motorista', 10, 2)->default(0);     // valor_motorista - descontos - adiantamento
            $table->decimal('lucro_transportadora', 10, 2)->default(0);
            $table->enum('status', ['aberta', 'em_andamento', 'aguardando_acerto', 'encerrada'])->default('aberta');
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viagens');
    }
};