<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viagem_id')->constrained('viagens')->onDelete('cascade');
            $table->enum('tipo', ['cte', 'mdfe', 'nfe', 'outros']);
            $table->string('numero', 50);
            $table->string('chave_acesso', 60)->nullable();
            $table->string('serie', 10)->nullable();
            $table->date('data_emissao');
            $table->decimal('valor', 10, 2)->default(0);
            $table->enum('status', ['pendente', 'autorizado', 'cancelado'])->default('pendente');
            $table->string('arquivo')->nullable(); // path do XML/PDF
            $table->text('observacao')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};