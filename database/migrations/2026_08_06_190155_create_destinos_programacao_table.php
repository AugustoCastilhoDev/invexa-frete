<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Destinos além do "Destino" principal da Programação (ex.: São Paulo →
    // Salvador → Maceió) — cada um vira sugestão de Carga (com destino já
    // preenchido) quando a Programação é confirmada em Viagem. carga_id
    // marca quando o operador já converteu essa sugestão numa Carga de
    // verdade, pra não continuar aparecendo como pendente.
    public function up(): void
    {
        Schema::create('destinos_programacao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->restrictOnDelete();
            $table->foreignId('programacao_viagem_id')->constrained('programacoes_viagem')->cascadeOnDelete();
            $table->string('cidade');
            $table->string('uf', 2);
            $table->string('codigo_municipio', 7)->nullable();
            $table->decimal('valor_frete', 10, 2)->nullable();
            $table->foreignId('carga_id')->nullable()->constrained('cargas')->nullOnDelete();
            $table->unsignedTinyInteger('ordem')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destinos_programacao');
    }
};
