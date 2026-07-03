<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('despesas_gerais', function (Blueprint $table) {
            $table->id();
            $table->enum('categoria', ['aluguel', 'salarios', 'contas', 'seguro', 'impostos', 'marketing', 'outros']);
            $table->string('descricao');
            $table->decimal('valor', 10, 2);
            $table->date('data_despesa');
            $table->boolean('recorrente')->default(false);
            $table->text('observacao')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('despesas_gerais');
    }
};
