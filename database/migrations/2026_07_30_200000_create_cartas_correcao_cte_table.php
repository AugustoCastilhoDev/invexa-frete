<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cartas_correcao_cte', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->restrictOnDelete();
            $table->foreignId('emissao_fiscal_id')->constrained('emissoes_fiscais')->cascadeOnDelete();
            $table->string('grupo_corrigido')->nullable();
            $table->string('campo_corrigido');
            $table->string('valor_corrigido', 500);
            $table->string('numero_item_grupo_corrigido')->nullable();
            $table->unsignedInteger('numero_carta_correcao')->nullable();
            $table->string('status_sefaz')->nullable();
            $table->string('mensagem_sefaz')->nullable();
            $table->string('caminho_xml')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cartas_correcao_cte');
    }
};
