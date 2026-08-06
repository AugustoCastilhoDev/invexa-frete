<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('viagens', function (Blueprint $table) {
            $table->foreignId('carreta_id')->nullable()->after('veiculo_id')
                ->constrained('veiculos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('viagens', function (Blueprint $table) {
            $table->dropConstrainedForeignId('carreta_id');
        });
    }
};
