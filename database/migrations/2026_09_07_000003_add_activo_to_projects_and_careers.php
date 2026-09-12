<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proyectos_titulacion', function (Blueprint $table) {
            $table->boolean('activo')->default(true)->after('estado');
        });

        Schema::table('carreras', function (Blueprint $table) {
            $table->boolean('activo')->default(true)->after('codigo');
        });
    }

    public function down(): void
    {
        Schema::table('proyectos_titulacion', fn (Blueprint $table) => $table->dropColumn('activo'));
        Schema::table('carreras', fn (Blueprint $table) => $table->dropColumn('activo'));
    }
};
