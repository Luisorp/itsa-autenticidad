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
        Schema::create('comparaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documento_a_id')->constrained('documentos')->cascadeOnDelete();
            $table->foreignId('documento_b_id')->constrained('documentos')->cascadeOnDelete();
            $table->decimal('porcentaje_similitud', 5, 2);
            $table->string('algoritmo_usado')->nullable();
            $table->timestamps();

            $table->unique(['documento_a_id', 'documento_b_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comparaciones');
    }
};
