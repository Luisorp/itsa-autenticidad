<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY rol ENUM('administrador','docente','estudiante','gestor','usuario') NOT NULL DEFAULT 'usuario'");
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->string('rol')->default('usuario')->change();
            });
        }

        DB::table('users')->where('rol', 'docente')->update(['rol' => 'gestor']);
        DB::table('users')->where('rol', 'estudiante')->update(['rol' => 'usuario']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY rol ENUM('administrador','gestor','usuario') NOT NULL DEFAULT 'usuario'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY rol ENUM('administrador','docente','estudiante','gestor','usuario') NOT NULL DEFAULT 'estudiante'");
        }

        DB::table('users')->where('rol', 'gestor')->update(['rol' => 'docente']);
        DB::table('users')->where('rol', 'usuario')->update(['rol' => 'estudiante']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY rol ENUM('administrador','docente','estudiante') NOT NULL DEFAULT 'estudiante'");
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->string('rol')->default('estudiante')->change();
            });
        }
    }
};
