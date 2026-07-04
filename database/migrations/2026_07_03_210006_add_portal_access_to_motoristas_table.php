<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('motoristas', function (Blueprint $table) {
            $table->string('password')->nullable()->after('email');
            $table->rememberToken()->after('password');
            $table->boolean('portal_ativo')->default(false)->after('remember_token');
        });
    }

    public function down(): void
    {
        Schema::table('motoristas', function (Blueprint $table) {
            $table->dropColumn(['password', 'remember_token', 'portal_ativo']);
        });
    }
};
