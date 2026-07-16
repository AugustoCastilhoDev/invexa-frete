<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emissoes_fiscais', function (Blueprint $table) {
            $table->foreignId('carga_id')->nullable()->after('viagem_id')
                ->constrained('cargas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('emissoes_fiscais', function (Blueprint $table) {
            $table->dropConstrainedForeignId('carga_id');
        });
    }
};
