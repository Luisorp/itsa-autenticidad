<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('rol', ['administrador', 'docente', 'estudiante'])->default('estudiante')->after('email');
            $table->foreignId('carrera_id')->nullable()->constrained('carreras')->nullOnDelete()->after('rol');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['carrera_id']);
            $table->dropColumn(['rol', 'carrera_id']);
        });
    }
};
