<?php

use App\Http\Controllers\AnalisisController;
use App\Http\Controllers\AnalisisExternoController;
use App\Http\Controllers\CarreraController;
use App\Http\Controllers\CrossrefController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocenteController;
use App\Http\Controllers\EstudianteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProyectoTitulacionController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\RespaldoController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/reportes', [ReporteController::class, 'index'])->middleware('auth')->name('reportes.index');
Route::get('/reportes/{reporte}/archivo', [ReporteController::class, 'verArchivo'])->middleware('auth')->name('reportes.archivo');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::resource('carreras', CarreraController::class)->except(['show', 'destroy'])->middleware(['auth', 'admin']);
    Route::patch('/carreras/{carrera}/toggle-activo', [CarreraController::class, 'toggleActivo'])->middleware(['auth', 'admin'])->name('carreras.toggle-activo');

    Route::resource('proyectos', ProyectoTitulacionController::class)->only(['index'])->middleware('auth');
    // Ruta analisis de proyectos
    Route::resource('proyectos', ProyectoTitulacionController::class)->only(['create', 'store', 'edit', 'update'])->middleware(['auth', 'rol:administrador,gestor']);
    Route::patch('/proyectos/{proyecto}/archivo', [ProyectoTitulacionController::class, 'toggleArchivado'])->middleware(['auth', 'admin'])->name('proyectos.archivo');
    Route::post('/proyectos/{proyecto}/analizar', [ProyectoTitulacionController::class, 'analizar'])->middleware(['auth', 'rol:administrador,gestor'])->name('proyectos.analizar');
    Route::get('/proyectos/{proyecto}/analisis-externo', [AnalisisExternoController::class, 'show'])->middleware(['auth', 'rol:administrador,gestor'])->name('proyectos.analisis-externo');
    Route::post('/proyectos/{proyecto}/analisis-externo', [AnalisisExternoController::class, 'analizar'])->middleware(['auth', 'rol:administrador,gestor'])->name('proyectos.analisis-externo.analizar');
    Route::get('/proyectos/{proyecto}/resultados', [ProyectoTitulacionController::class, 'resultados'])->middleware('auth')->name('proyectos.resultados');
    Route::get('/proyectos/{proyecto}/documento', [ProyectoTitulacionController::class, 'verDocumento'])->middleware('auth')->name('proyectos.documento');
    Route::get('/proyectos/{proyecto}/reporte', [ProyectoTitulacionController::class, 'reporte'])->middleware('auth')->name('proyectos.reporte');
    // seccion nueva de analisis de dos proyectos
    Route::middleware('rol:administrador,gestor')->group(function () {
        Route::get('/analizar', [AnalisisController::class, 'index'])->name('analisis.index');
        Route::get('/analizar/proyectos/buscar', [AnalisisController::class, 'buscarProyectos'])->name('analisis.proyectos.buscar');
        Route::post('/analizar', [AnalisisController::class, 'comparar'])->name('analisis.comparar');
        Route::get('/crossref', [CrossrefController::class, 'index'])->name('crossref.index');
        Route::post('/crossref/comparar', [CrossrefController::class, 'comparar'])->name('crossref.comparar');
        Route::get('/proyectos-catalogo/estudiantes', [ProyectoTitulacionController::class, 'buscarEstudiantes'])->name('proyectos.estudiantes.buscar');
        Route::get('/proyectos-catalogo/docentes', [ProyectoTitulacionController::class, 'buscarDocentes'])->name('proyectos.docentes.buscar');

        Route::resource('estudiantes', EstudianteController::class)->except(['show', 'destroy']);
        Route::patch('/estudiantes/{estudiante}/toggle-activo', [EstudianteController::class, 'toggleActivo'])->name('estudiantes.toggle-activo');
        Route::post('/estudiantes/importar', [EstudianteController::class, 'importar'])->name('estudiantes.importar');
        Route::get('/estudiantes-plantilla.csv', [EstudianteController::class, 'plantilla'])->name('estudiantes.plantilla');

        Route::resource('docentes', DocenteController::class)->except(['show', 'destroy']);
        Route::patch('/docentes/{docente}/toggle-activo', [DocenteController::class, 'toggleActivo'])->name('docentes.toggle-activo');
    });

});
// Ruta Usuario controler mas el mideware par el acceso de administrador
Route::middleware(['auth', 'admin'])->group(function () {
    Route::resource('usuarios', UsuarioController::class)->except(['show', 'destroy']);
    Route::patch('/usuarios/{usuario}/toggle-activo', [UsuarioController::class, 'toggleActivo'])->name('usuarios.toggle-activo');

    Route::get('/respaldos', [RespaldoController::class, 'index'])->name('respaldos.index');
    Route::post('/respaldos/generar', [RespaldoController::class, 'generar'])->name('respaldos.generar');
    Route::get('/respaldos/{archivo}/descargar', [RespaldoController::class, 'descargar'])->name('respaldos.descargar');
});

require __DIR__.'/auth.php';
