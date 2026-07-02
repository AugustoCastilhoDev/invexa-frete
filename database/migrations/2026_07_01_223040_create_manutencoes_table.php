<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manutencoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veiculo_id')->constrained('veiculos')->cascadeOnDelete();
            $table->enum('tipo', ['preventiva', 'corretiva']);
            $table->string('descricao');
            $table->date('data_manutencao');
            $table->integer('km_veiculo')->nullable();
            $table->decimal('valor', 10, 2)->default(0);
            $table->date('proxima_manutencao_data')->nullable();
            $table->integer('proxima_manutencao_km')->nullable();
            $table->enum('status', ['em_andamento', 'concluida'])->default('concluida');
            $table->text('observacao')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manutencoes');
    }
};
