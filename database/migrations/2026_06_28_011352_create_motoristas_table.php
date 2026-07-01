<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('motoristas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('cpf', 14)->unique();
            $table->string('cnh', 20)->nullable();
            $table->string('categoria_cnh', 5)->nullable(); // A, B, C, D, E
            $table->date('validade_cnh')->nullable();
            $table->string('telefone', 20)->nullable();
            $table->string('email')->nullable();
            $table->decimal('percentual_comissao', 5, 2)->default(0); // % padrão sobre o frete
            $table->enum('status', ['ativo', 'inativo'])->default('ativo');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('motoristas');
    }
};