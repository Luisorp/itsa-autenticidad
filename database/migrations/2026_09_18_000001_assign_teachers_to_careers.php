<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carrera_docente', function (Blueprint $table) {
            $table->foreignId('carrera_id')->constrained('carreras')->cascadeOnDelete();
            $table->foreignId('docente_id')->constrained('docentes')->cascadeOnDelete();
            $table->primary(['carrera_id', 'docente_id']);
        });

        $asignaciones = DB::table('proyectos_titulacion')
            ->whereNotNull('tutor_id')
            ->select(['carrera_id', 'tutor_id as docente_id'])
            ->distinct()
            ->get()
            ->map(fn ($fila) => (array) $fila)
            ->all();

        foreach (DB::table('docentes')->whereNotNull('user_id')->get() as $docente) {
            $carreraId = DB::table('users')->where('id', $docente->user_id)->value('carrera_id');
            if ($carreraId) {
                $asignaciones[] = ['carrera_id' => $carreraId, 'docente_id' => $docente->id];
            }
        }

        if ($asignaciones !== []) {
            DB::table('carrera_docente')->insertOrIgnore($asignaciones);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('carrera_docente');
    }
};
