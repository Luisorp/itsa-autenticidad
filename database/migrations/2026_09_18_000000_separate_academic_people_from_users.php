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
        Schema::create('estudiantes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->nullable()->unique();
            $table->string('nombre');
            $table->string('email')->nullable();
            $table->foreignId('carrera_id')->nullable()->constrained('carreras')->nullOnDelete();
            $table->unsignedSmallInteger('gestion_ingreso')->nullable();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('docentes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->nullable()->unique();
            $table->string('nombre');
            $table->string('email')->nullable();
            $table->string('especialidad')->nullable();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        $estudianteIds = DB::table('users')->where('rol', 'usuario')->pluck('id')
            ->merge(DB::table('proyectos_titulacion')->pluck('estudiante_id'))
            ->filter()->unique();

        foreach (DB::table('users')->whereIn('id', $estudianteIds)->get() as $user) {
            $carreraId = $user->carrera_id
                ?? DB::table('proyectos_titulacion')->where('estudiante_id', $user->id)->value('carrera_id');

            DB::table('estudiantes')->insert([
                'id' => $user->id,
                'codigo' => null,
                'nombre' => $user->name,
                'email' => $user->email,
                'carrera_id' => $carreraId,
                'gestion_ingreso' => null,
                'user_id' => $user->id,
                'activo' => $user->activo ?? true,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]);
        }

        $docenteIds = DB::table('users')->where('rol', 'gestor')->pluck('id')
            ->merge(DB::table('proyectos_titulacion')->whereNotNull('tutor_id')->pluck('tutor_id'))
            ->filter()->unique();

        foreach (DB::table('users')->whereIn('id', $docenteIds)->get() as $user) {
            DB::table('docentes')->insert([
                'id' => $user->id,
                'codigo' => null,
                'nombre' => $user->name,
                'email' => $user->email,
                'especialidad' => null,
                'user_id' => $user->id,
                'activo' => $user->activo ?? true,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]);
        }

        Schema::table('proyectos_titulacion', function (Blueprint $table) {
            $table->dropForeign(['estudiante_id']);
            $table->dropForeign(['tutor_id']);
        });

        Schema::table('proyectos_titulacion', function (Blueprint $table) {
            $table->foreign('estudiante_id')->references('id')->on('estudiantes');
            $table->foreign('tutor_id')->references('id')->on('docentes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        foreach (DB::table('estudiantes')->whereNull('user_id')->get() as $estudiante) {
            $userId = DB::table('users')->insertGetId([
                'name' => $estudiante->nombre,
                'email' => 'estudiante-'.$estudiante->id.'@rollback.local',
                'rol' => 'usuario',
                'carrera_id' => $estudiante->carrera_id,
                'password' => Hash::make(Str::random(40)),
                'activo' => $estudiante->activo,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('estudiantes')->where('id', $estudiante->id)->update(['user_id' => $userId]);
        }

        foreach (DB::table('docentes')->whereNull('user_id')->get() as $docente) {
            $userId = DB::table('users')->insertGetId([
                'name' => $docente->nombre,
                'email' => 'docente-'.$docente->id.'@rollback.local',
                'rol' => 'gestor',
                'password' => Hash::make(Str::random(40)),
                'activo' => $docente->activo,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('docentes')->where('id', $docente->id)->update(['user_id' => $userId]);
        }

        Schema::table('proyectos_titulacion', function (Blueprint $table) {
            $table->dropForeign(['estudiante_id']);
            $table->dropForeign(['tutor_id']);
        });

        foreach (DB::table('proyectos_titulacion')->get() as $proyecto) {
            DB::table('proyectos_titulacion')->where('id', $proyecto->id)->update([
                'estudiante_id' => DB::table('estudiantes')->where('id', $proyecto->estudiante_id)->value('user_id'),
                'tutor_id' => $proyecto->tutor_id
                    ? DB::table('docentes')->where('id', $proyecto->tutor_id)->value('user_id')
                    : null,
            ]);
        }

        Schema::table('proyectos_titulacion', function (Blueprint $table) {
            $table->foreign('estudiante_id')->references('id')->on('users');
            $table->foreign('tutor_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::dropIfExists('docentes');
        Schema::dropIfExists('estudiantes');
    }
};
