<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proyectos_titulacion', function (Blueprint $table) {
            $table->string('modalidad', 50)
                ->default('proyecto_grado')
                ->after('resumen');
        });
    }

    public function down(): void
    {
        Schema::table('proyectos_titulacion', function (Blueprint $table) {
            $table->dropColumn('modalidad');
        });
    }
};
