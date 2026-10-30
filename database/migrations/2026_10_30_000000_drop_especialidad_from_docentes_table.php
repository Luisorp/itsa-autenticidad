<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('docentes', 'especialidad')) {
            return;
        }

        $hayDatos = DB::table('docentes')
            ->whereNotNull('especialidad')
            ->where('especialidad', '!=', '')
            ->exists();

        if ($hayDatos) {
            throw new RuntimeException(
                'No se eliminó docentes.especialidad porque contiene datos. Respalda o revisa esos valores antes de volver a ejecutar las migraciones.'
            );
        }

        Schema::table('docentes', function (Blueprint $table) {
            $table->dropColumn('especialidad');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('docentes') && ! Schema::hasColumn('docentes', 'especialidad')) {
            Schema::table('docentes', function (Blueprint $table) {
                $table->string('especialidad')->nullable();
            });
        }
    }
};
