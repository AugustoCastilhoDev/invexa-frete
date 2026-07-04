<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $empresaPadraoId = DB::table('empresas')->where('nome', 'Empresa Padrão')->value('id');

        Schema::table('descontos', function (Blueprint $table) use ($empresaPadraoId) {
            $table->foreignId('empresa_id')->after('id')->default($empresaPadraoId)->constrained('empresas')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('descontos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('empresa_id');
        });
    }
};
