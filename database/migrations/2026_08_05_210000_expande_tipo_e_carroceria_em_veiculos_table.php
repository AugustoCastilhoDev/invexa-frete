<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Doctrine DBAL não lida bem com ALTER de enum — recria a coluna via SQL bruto,
        // preservando os dados. MySQL só (produção/homologação); SQLite (testes) trata
        // enum como texto livre, então roda direto.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE veiculos MODIFY tipo ENUM('truck','bitruck','cavalo_simples','cavalo_trucado','carreta','bitrem_rodotrem','van','utilitario','outro') NOT NULL DEFAULT 'outro'");
        }

        // 'truck' antes significava "unidade tratora" (usado pra puxar carreta, ver
        // cavalo_id) — na taxonomia nova isso é 'cavalo_simples', e 'truck' passa a
        // significar chassi rígido (não traciona). Sem essa migração de dado, todo
        // cavalo cadastrado antes de hoje sumiria da lista de "cavalo disponível".
        DB::table('veiculos')->where('tipo', 'truck')->update(['tipo' => 'cavalo_simples']);

        Schema::table('veiculos', function (Blueprint $table) {
            $table->enum('tipo_carroceria', ['bau_sider', 'graneleiro', 'cacamba', 'prancha_container'])
                ->nullable()
                ->after('tipo');
        });
    }

    public function down(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            $table->dropColumn('tipo_carroceria');
        });

        DB::table('veiculos')->whereIn('tipo', ['bitruck', 'cavalo_trucado', 'bitrem_rodotrem'])->update(['tipo' => 'outro']);
        DB::table('veiculos')->where('tipo', 'cavalo_simples')->update(['tipo' => 'truck']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE veiculos MODIFY tipo ENUM('truck','carreta','van','utilitario','outro') NOT NULL DEFAULT 'outro'");
        }
    }
};
