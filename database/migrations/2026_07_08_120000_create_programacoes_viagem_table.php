<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programacoes_viagem', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->restrictOnDelete();
            $table->foreignId('motorista_id')->constrained('motoristas');
            $table->foreignId('veiculo_id')->constrained('veiculos');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('viagem_origem_id')->nullable()->constrained('viagens')->nullOnDelete();
            $table->foreignId('viagem_id')->nullable()->constrained('viagens')->nullOnDelete();
            $table->string('origem');
            $table->string('destino');
            $table->date('data_prevista');
            $table->text('observacoes')->nullable();
            $table->enum('status', ['pendente', 'confirmada'])->default('pendente');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programacoes_viagem');
    }
};
