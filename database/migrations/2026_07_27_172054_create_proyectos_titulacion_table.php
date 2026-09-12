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
        Schema::create('proyectos_titulacion', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('resumen')->nullable();
            $table->foreignId('carrera_id')->constrained('carreras');
            $table->foreignId('estudiante_id')->constrained('users');
            $table->foreignId('tutor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedSmallInteger('anio');
            $table->enum('estado', ['pendiente_analisis', 'analizado'])->default('pendiente_analisis');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proyectos_titulacion');
    }
};
