<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proyectos_titulacion', function (Blueprint $table) {
            // Nullable to preserve historical records without inventing their period.
            $table->string('periodo_academico', 20)->nullable()->after('anio');
        });
    }

    public function down(): void
    {
        Schema::table('proyectos_titulacion', function (Blueprint $table) {
            $table->dropColumn('periodo_academico');
        });
    }
};
