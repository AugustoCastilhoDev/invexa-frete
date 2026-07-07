<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * 'bonificacao' é um crédito ao motorista (diária, bônus), ao contrário dos
     * demais tipos, que são débitos.
     */
    public function up(): void
    {
        Schema::table('descontos', function (Blueprint $table) {
            $table->enum('tipo', ['vale', 'multa', 'adiantamento', 'outros', 'bonificacao'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('descontos', function (Blueprint $table) {
            $table->enum('tipo', ['vale', 'multa', 'adiantamento', 'outros'])->change();
        });
    }
};
