<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->boolean('focus_nfe_ativo')->default(false)->after('asaas_last_event_at');
            $table->enum('focus_nfe_ambiente', ['homologacao', 'producao'])->nullable()->after('focus_nfe_ativo');
            $table->string('focus_nfe_empresa_id')->nullable()->after('focus_nfe_ambiente');
            $table->string('focus_nfe_token')->nullable()->after('focus_nfe_empresa_id');
            $table->string('focus_nfe_status')->nullable()->after('focus_nfe_token');
            $table->string('focus_nfe_certificado_path')->nullable()->after('focus_nfe_status');
            $table->string('focus_nfe_certificado_senha')->nullable()->after('focus_nfe_certificado_path');
            $table->date('focus_nfe_certificado_validade')->nullable()->after('focus_nfe_certificado_senha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn([
                'focus_nfe_ativo',
                'focus_nfe_ambiente',
                'focus_nfe_empresa_id',
                'focus_nfe_token',
                'focus_nfe_status',
                'focus_nfe_certificado_path',
                'focus_nfe_certificado_senha',
                'focus_nfe_certificado_validade',
            ]);
        });
    }
};
