<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('empresa_id')->nullable()->after('id')->constrained('empresas')->nullOnDelete();
            $table->enum('role', ['admin', 'operador', 'super_admin'])->default('operador')->change();
        });

        // Os dados atuais são de teste: viram a "Empresa Padrão", e o admin
        // existente passa a ser um admin comum dessa empresa.
        $empresaPadraoId = DB::table('empresas')->insertGetId([
            'nome'       => 'Empresa Padrão',
            'status'     => 'ativo',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->whereNull('empresa_id')->update(['empresa_id' => $empresaPadraoId]);

        // Usuário "super admin" da plataforma (Castilho Soluções Digitais), sem
        // empresa própria — gerencia as empresas clientes. Sem senha utilizável
        // ainda: o acesso é reivindicado via "esqueci minha senha".
        DB::table('users')->insert([
            'empresa_id'         => null,
            'name'               => 'Castilho Soluções Digitais',
            'email'              => 'ac.castilho87@gmail.com',
            'email_verified_at'  => now(),
            'password'           => Hash::make(Str::random(40)),
            'role'               => 'super_admin',
            'status'             => 'ativo',
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'super_admin')->delete();

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'operador'])->default('operador')->change();
            $table->dropConstrainedForeignId('empresa_id');
        });
    }
};
