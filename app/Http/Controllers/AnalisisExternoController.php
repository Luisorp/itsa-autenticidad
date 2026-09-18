<?php

namespace App\Http\Controllers;

use App\Models\ProyectoTitulacion;
use App\Services\AnalisisExternoService;

class AnalisisExternoController extends Controller
{
    public function show(ProyectoTitulacion $proyecto)
    {
        $this->validarProyecto($proyecto);

        return redirect()->route('crossref.index', ['proyecto_id' => $proyecto->id]);
    }

    public function analizar(
        ProyectoTitulacion $proyecto,
        AnalisisExternoService $analisis
    ) {
        $this->validarProyecto($proyecto);
        ['resultados' => $resultados, 'fuentesNoDisponibles' => $fuentesNoDisponibles] = $analisis->comparar($proyecto);

        return view('crossref.index', compact('proyecto', 'resultados', 'fuentesNoDisponibles'));
    }

    private function validarProyecto(ProyectoTitulacion $proyecto): void
    {
        abort_unless($proyecto->activo, 404);
        $proyecto->loadMissing(['documento', 'estudiante', 'carrera']);
        abort_if(
            ! $proyecto->documento || trim((string) $proyecto->documento->contenido_extraido) === '',
            422,
            'El proyecto no tiene texto extraído disponible para el análisis externo.'
        );
    }
}
