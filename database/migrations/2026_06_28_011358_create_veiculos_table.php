<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('veiculos', function (Blueprint $table) {
            $table->id();
            $table->string('placa', 10)->unique();
            $table->string('modelo');
            $table->string('marca')->nullable();
            $table->year('ano')->nullable();
            $table->enum('tipo', ['truck', 'carreta', 'van', 'utilitario', 'outro'])->default('outro');
            $table->string('renavam', 20)->nullable();
            $table->decimal('capacidade_kg', 10, 2)->nullable();
            $table->enum('status', ['ativo', 'inativo', 'manutencao'])->default('ativo');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('veiculos');
    }
};