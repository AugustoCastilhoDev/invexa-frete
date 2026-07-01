<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lancamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viagem_id')->constrained('viagens')->onDelete('cascade');
            $table->enum('tipo', ['combustivel', 'manutencao', 'outros']);
            $table->string('descricao');
            $table->decimal('valor', 10, 2);
            $table->date('data_lancamento');
            $table->string('comprovante')->nullable(); // path do arquivo
            $table->text('observacao')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lancamentos');
    }
};