<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emissoes_fiscais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->restrictOnDelete();
            $table->foreignId('viagem_id')->constrained('viagens')->restrictOnDelete();
            $table->foreignId('documento_id')->nullable()->constrained('documentos')->nullOnDelete();
            $table->enum('tipo', ['cte', 'mdfe']);
            $table->string('referencia', 60);
            $table->string('status', 40)->default('processando_autorizacao');
            $table->string('chave_acesso', 44)->nullable();
            $table->string('numero', 20)->nullable();
            $table->string('serie', 10)->nullable();
            $table->string('protocolo_autorizacao')->nullable();
            $table->string('codigo_erro')->nullable();
            $table->text('mensagem_erro')->nullable();
            $table->json('payload_enviado')->nullable();
            $table->json('payload_resposta')->nullable();
            $table->string('arquivo_xml')->nullable();
            $table->string('arquivo_pdf')->nullable();
            $table->timestamp('autorizado_em')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'referencia']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emissoes_fiscais');
    }
};
