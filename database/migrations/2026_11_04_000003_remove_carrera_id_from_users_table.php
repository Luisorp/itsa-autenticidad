<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Refuse to discard career assignments that belong to academic profiles
        // but were not migrated to their normalized profile relationships.
        $orphanedAssignments = DB::table('users')
            ->whereNotNull('carrera_id')
            ->where(function ($query) {
                $query->where(function ($student) {
                    $student->where('rol', 'usuario')->whereNotExists(fn ($query) => $query->selectRaw('1')
                        ->from('estudiantes')->whereColumn('estudiantes.user_id', 'users.id')
                        ->whereNotNull('estudiantes.carrera_id'));
                })->orWhere(function ($teacher) {
                    $teacher->where('rol', 'gestor')->whereNotExists(fn ($query) => $query->selectRaw('1')
                        ->from('docentes')->join('carrera_docente', 'carrera_docente.docente_id', '=', 'docentes.id')
                        ->whereColumn('docentes.user_id', 'users.id'));
                });
            })
            ->pluck('id');

        if ($orphanedAssignments->isNotEmpty()) {
            throw new \RuntimeException(
                'No se eliminó users.carrera_id: hay cuentas con carrera asignada y sin perfil académico vinculado. Revise los IDs: '
                .$orphanedAssignments->implode(', ')
            );
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['carrera_id']);
            $table->dropColumn('carrera_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('carrera_id')->nullable()->constrained('carreras')->nullOnDelete();
        });

        foreach (DB::table('estudiantes')->whereNotNull('user_id')->whereNotNull('carrera_id')->get(['user_id', 'carrera_id']) as $estudiante) {
            DB::table('users')->where('id', $estudiante->user_id)
                ->update(['carrera_id' => $estudiante->carrera_id]);
        }

        foreach (DB::table('docentes')->whereNotNull('user_id')->get(['id', 'user_id']) as $docente) {
            $carreraId = DB::table('carrera_docente')->where('docente_id', $docente->id)->value('carrera_id');
            if ($carreraId) {
                DB::table('users')->where('id', $docente->user_id)->whereNull('carrera_id')
                    ->update(['carrera_id' => $carreraId]);
            }
        }
    }
};
