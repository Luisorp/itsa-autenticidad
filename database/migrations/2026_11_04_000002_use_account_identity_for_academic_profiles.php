<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->string('nombre')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::table('docentes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->string('nombre')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
        });

        // A linked account is the single source of its person's name and email.
        DB::table('estudiantes')->whereNotNull('user_id')->update(['nombre' => null, 'email' => null]);
        DB::table('docentes')->whereNotNull('user_id')->update(['nombre' => null, 'email' => null]);
    }

    public function down(): void
    {
        DB::table('estudiantes')->whereNotNull('user_id')->update([
            'nombre' => DB::raw('(SELECT name FROM users WHERE users.id = estudiantes.user_id)'),
            'email' => DB::raw('(SELECT email FROM users WHERE users.id = estudiantes.user_id)'),
        ]);
        DB::table('docentes')->whereNotNull('user_id')->update([
            'nombre' => DB::raw('(SELECT name FROM users WHERE users.id = docentes.user_id)'),
            'email' => DB::raw('(SELECT email FROM users WHERE users.id = docentes.user_id)'),
        ]);

        Schema::table('estudiantes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->string('nombre')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('docentes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->string('nombre')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }
};
