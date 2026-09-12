<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $ids = DB::table('comparaciones as comparacion')
            ->join('documentos as documento_a', 'documento_a.id', '=', 'comparacion.documento_a_id')
            ->join('documentos as documento_b', 'documento_b.id', '=', 'comparacion.documento_b_id')
            ->join('proyectos_titulacion as proyecto_a', 'proyecto_a.id', '=', 'documento_a.proyecto_id')
            ->join('proyectos_titulacion as proyecto_b', 'proyecto_b.id', '=', 'documento_b.proyecto_id')
            ->whereColumn('proyecto_a.modalidad', '!=', 'proyecto_b.modalidad')
            ->pluck('comparacion.id');

        DB::table('comparaciones')->whereIn('id', $ids)->delete();
    }

    public function down(): void
    {
        // Las comparaciones eliminadas son datos derivados y pueden regenerarse.
    }
};
