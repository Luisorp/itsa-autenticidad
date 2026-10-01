<?php

namespace App\Http\Controllers;

use App\Models\ProyectoTitulacion;
use App\Services\AnalisisExternoService;
use App\Services\SeccionesDocumentoService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CrossrefController extends Controller
{
    public function index(Request $request, SeccionesDocumentoService $secciones)
    {
        $proyecto = $this->proyectoSeleccionable($request->old('proyecto_id', $request->input('proyecto_id')), $secciones);

        return view('crossref.index', compact('proyecto'));
    }

    public function comparar(Request $request, AnalisisExternoService $analisis, SeccionesDocumentoService $secciones)
    {
        $validated = $request->validate([
            'proyecto_id' => ['required', 'integer', 'exists:proyectos_titulacion,id'],
        ]);
        $proyecto = ProyectoTitulacion::with(['documento', 'estudiante', 'carrera'])
            ->where('activo', true)
            ->findOrFail($validated['proyecto_id']);
        $this->validarTexto($proyecto, $secciones);

        ['resultados' => $resultados, 'fuentesNoDisponibles' => $fuentesNoDisponibles] = $analisis->comparar($proyecto);

        return view('crossref.index', compact('proyecto', 'resultados', 'fuentesNoDisponibles'));
    }

    private function proyectoSeleccionable(mixed $id, SeccionesDocumentoService $secciones): ?ProyectoTitulacion
    {
        if (! is_numeric($id)) {
            return null;
        }

        $proyecto = ProyectoTitulacion::with(['documento', 'estudiante', 'carrera'])
            ->where('activo', true)
            ->whereHas('documento', fn ($documento) => $documento->whereNotNull('contenido_extraido')->where('contenido_extraido', '!=', ''))
            ->find((int) $id);

        return $proyecto && $secciones->tieneContenidoAnalizable((string) $proyecto->documento?->contenido_extraido)
            ? $proyecto
            : null;
    }

    private function validarTexto(ProyectoTitulacion $proyecto, SeccionesDocumentoService $secciones): void
    {
        if (! $proyecto->documento || ! $secciones->tieneContenidoAnalizable((string) $proyecto->documento->contenido_extraido)) {
            throw ValidationException::withMessages([
                'proyecto_id' => 'El proyecto seleccionado no contiene secciones analizables (Resumen, Introducción, Marco teórico, Desarrollo/Propuesta o Conclusiones).',
            ]);
        }
    }
}
