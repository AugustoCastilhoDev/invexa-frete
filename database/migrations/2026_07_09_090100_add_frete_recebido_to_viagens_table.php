<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('viagens', function (Blueprint $table) {
            $table->boolean('frete_recebido')->default(false)->after('valor_frete');
            $table->date('data_recebimento_frete')->nullable()->after('frete_recebido');
        });
    }

    public function down(): void
    {
        Schema::table('viagens', function (Blueprint $table) {
            $table->dropColumn(['frete_recebido', 'data_recebimento_frete']);
        });
    }
};
