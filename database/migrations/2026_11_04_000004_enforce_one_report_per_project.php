<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicateProjectIds = DB::table('reportes')
            ->select('proyecto_id')
            ->groupBy('proyecto_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('proyecto_id');

        if ($duplicateProjectIds->isNotEmpty()) {
            throw new \RuntimeException(
                'No se agregó la restricción de reportes: hay varios reportes para los proyectos '
                .$duplicateProjectIds->implode(', ').'. Revise esos registros sin eliminar datos automáticamente.'
            );
        }

        Schema::table('reportes', function (Blueprint $table) {
            $table->unique('proyecto_id');
        });
    }

    public function down(): void
    {
        Schema::table('reportes', function (Blueprint $table) {
            $table->dropUnique(['proyecto_id']);
        });
    }
};
