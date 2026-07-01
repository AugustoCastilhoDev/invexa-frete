<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'operador'])->default('operador')->after('password');
            $table->enum('status', ['ativo', 'inativo'])->default('ativo')->after('role');
            $table->softDeletes();
        });

        // O primeiro usuário cadastrado (normalmente o dono da conta) vira admin
        // automaticamente, para ninguém ficar sem acesso à gestão de usuários.
        DB::table('users')->orderBy('id')->limit(1)->update(['role' => 'admin']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'status', 'deleted_at']);
        });
    }
};
