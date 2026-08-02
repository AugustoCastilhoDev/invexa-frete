<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('despesas_gerais', function (Blueprint $table) {
            $table->foreignId('veiculo_id')->nullable()->after('empresa_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('despesas_gerais', function (Blueprint $table) {
            $table->dropConstrainedForeignId('veiculo_id');
        });
    }
};
