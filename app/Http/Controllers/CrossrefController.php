<?php

namespace App\Http\Controllers;

use App\Models\ProyectoTitulacion;
use App\Services\AnalisisExternoService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CrossrefController extends Controller
{
    public function index(Request $request)
    {
        $proyecto = $this->proyectoSeleccionable($request->old('proyecto_id', $request->input('proyecto_id')));

        return view('crossref.index', compact('proyecto'));
    }

    public function comparar(Request $request, AnalisisExternoService $analisis)
    {
        $validated = $request->validate([
            'proyecto_id' => ['required', 'integer', 'exists:proyectos_titulacion,id'],
        ]);
        $proyecto = ProyectoTitulacion::with(['documento', 'estudiante', 'carrera'])
            ->where('activo', true)
            ->findOrFail($validated['proyecto_id']);
        $this->validarTexto($proyecto);

        ['resultados' => $resultados, 'fuentesNoDisponibles' => $fuentesNoDisponibles] = $analisis->comparar($proyecto);

        return view('crossref.index', compact('proyecto', 'resultados', 'fuentesNoDisponibles'));
    }

    private function proyectoSeleccionable(mixed $id): ?ProyectoTitulacion
    {
        if (! is_numeric($id)) {
            return null;
        }

        return ProyectoTitulacion::with(['documento', 'estudiante', 'carrera'])
            ->where('activo', true)
            ->whereHas('documento', fn ($documento) => $documento->whereNotNull('contenido_extraido')->where('contenido_extraido', '!=', ''))
            ->find((int) $id);
    }

    private function validarTexto(ProyectoTitulacion $proyecto): void
    {
        if (! $proyecto->documento || trim((string) $proyecto->documento->contenido_extraido) === '') {
            throw ValidationException::withMessages([
                'proyecto_id' => 'El proyecto seleccionado no tiene texto extraído disponible para comparar.',
            ]);
        }
    }
}
