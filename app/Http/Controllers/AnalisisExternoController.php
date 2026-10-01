<?php

namespace App\Http\Controllers;

use App\Models\ProyectoTitulacion;
use App\Services\AnalisisExternoService;
use App\Services\SeccionesDocumentoService;

class AnalisisExternoController extends Controller
{
    public function show(ProyectoTitulacion $proyecto, SeccionesDocumentoService $secciones)
    {
        $this->validarProyecto($proyecto, $secciones);

        return redirect()->route('crossref.index', ['proyecto_id' => $proyecto->id]);
    }

    public function analizar(
        ProyectoTitulacion $proyecto,
        AnalisisExternoService $analisis,
        SeccionesDocumentoService $secciones,
    ) {
        $this->validarProyecto($proyecto, $secciones);
        ['resultados' => $resultados, 'fuentesNoDisponibles' => $fuentesNoDisponibles] = $analisis->comparar($proyecto);

        return view('crossref.index', compact('proyecto', 'resultados', 'fuentesNoDisponibles'));
    }

    private function validarProyecto(ProyectoTitulacion $proyecto, SeccionesDocumentoService $secciones): void
    {
        abort_unless($proyecto->activo, 404);
        $proyecto->loadMissing(['documento', 'estudiante', 'carrera']);
        abort_if(
            ! $proyecto->documento || ! $secciones->tieneContenidoAnalizable((string) $proyecto->documento->contenido_extraido),
            422,
            'El proyecto no contiene secciones autorizadas para el análisis externo.'
        );
    }
}
