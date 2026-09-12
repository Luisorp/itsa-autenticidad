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
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('proyectos_titulacion')->cascadeOnDelete()->unique();
            $table->string('nombre_archivo');
            $table->string('ruta_archivo');
            $table->string('tipo_archivo', 10)->nullable();
            $table->unsignedBigInteger('tamano_archivo')->nullable();
            $table->string('hash_archivo', 64)->nullable()->index();
            $table->longText('contenido_extraido')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
