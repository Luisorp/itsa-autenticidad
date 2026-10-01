<?php

// seccion de analisi de dos proyectos

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\ProyectoTitulacion;
use App\Services\SeccionesDocumentoService;
use App\Services\SimilitudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AnalisisController extends Controller
{
    public function index(Request $request, SeccionesDocumentoService $secciones)
    {
        $proyectoA = $this->proyectoSeleccionable($request->old('proyecto_a', $request->input('proyecto_a')), $secciones);
        $proyectoB = $this->proyectoSeleccionable($request->old('proyecto_b'), $secciones);

        return view('analisis.index', compact('proyectoA', 'proyectoB'));
    }

    public function buscarProyectos(Request $request, SeccionesDocumentoService $secciones): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'modalidad' => ['nullable', Rule::in(array_keys(ProyectoTitulacion::MODALIDADES))],
            'excluir' => ['nullable', 'integer'],
        ]);

        $termino = trim($validated['q'] ?? '');
        $query = ProyectoTitulacion::query()
            ->select(['id', 'titulo', 'modalidad', 'carrera_id', 'estudiante_id', 'anio'])
            ->where('activo', true)
            ->whereHas('documento', fn ($q) => $q->whereNotNull('contenido_extraido')->where('contenido_extraido', '!=', ''))
            ->with(['estudiante:id,nombre', 'carrera:id,nombre,codigo', 'documento:id,proyecto_id,contenido_extraido']);

        if ($termino !== '') {
            $query->where(function ($q) use ($termino) {
                $q->where('titulo', 'like', "%{$termino}%")
                    ->orWhereHas('estudiante', fn ($estudiante) => $estudiante->where('nombre', 'like', "%{$termino}%"))
                    ->orWhereHas('carrera', function ($carrera) use ($termino) {
                        $carrera->where('nombre', 'like', "%{$termino}%")
                            ->orWhere('codigo', 'like', "%{$termino}%");
                    });
            });
        }

        if (! empty($validated['modalidad'])) {
            $query->where('modalidad', $validated['modalidad']);
        }

        if (! empty($validated['excluir'])) {
            $query->where('id', '!=', $validated['excluir']);
        }

        $proyectos = $query->orderBy('titulo')->limit(30)->get()
            ->filter(fn ($proyecto) => $secciones->tieneContenidoAnalizable((string) $proyecto->documento?->contenido_extraido))
            ->take(15)->values()->map(fn ($proyecto) => [
                'id' => $proyecto->id,
                'titulo' => $proyecto->titulo,
                'modalidad' => $proyecto->modalidad,
                'modalidad_nombre' => $proyecto->modalidad_nombre,
                'estudiante' => $proyecto->estudiante?->nombre ?? 'Sin estudiante',
                'carrera' => $proyecto->carrera?->codigo ?? $proyecto->carrera?->nombre ?? 'Sin carrera',
                'anio' => $proyecto->anio,
            ]);

        return response()->json(['data' => $proyectos]);
    }

    public function comparar(Request $request, SeccionesDocumentoService $secciones)
    {
        $validated = $request->validate([
            'proyecto_a' => 'required|exists:proyectos_titulacion,id|different:proyecto_b',
            'proyecto_b' => 'required|exists:proyectos_titulacion,id',
        ]);

        $proyectoA = ProyectoTitulacion::where('activo', true)->with('documento')->findOrFail($validated['proyecto_a']);
        $proyectoB = ProyectoTitulacion::where('activo', true)->with('documento')->findOrFail($validated['proyecto_b']);

        $errores = [];
        $contenidoA = $proyectoA->documento ? $secciones->extraer((string) $proyectoA->documento->contenido_extraido) : null;
        $contenidoB = $proyectoB->documento ? $secciones->extraer((string) $proyectoB->documento->contenido_extraido) : null;
        if (! $contenidoA || $contenidoA['texto'] === '') {
            $errores['proyecto_a'] = 'El Proyecto A no contiene secciones analizables (Resumen, Introducción, Marco teórico, Desarrollo/Propuesta o Conclusiones).';
        }
        if (! $contenidoB || $contenidoB['texto'] === '') {
            $errores['proyecto_b'] = 'El Proyecto B no contiene secciones analizables (Resumen, Introducción, Marco teórico, Desarrollo/Propuesta o Conclusiones).';
        }
        if ($proyectoA->modalidad !== $proyectoB->modalidad) {
            $errores['proyecto_b'] = 'Solo se pueden comparar proyectos de la misma modalidad de graduación.';
        }
        if ($errores !== []) {
            throw ValidationException::withMessages($errores);
        }

        $corpus = Documento::whereNotNull('contenido_extraido')
            ->whereHas('proyecto', fn ($query) => $query->where('modalidad', $proyectoA->modalidad)->where('activo', true))
            ->get(['id', 'contenido_extraido'])
            ->mapWithKeys(fn (Documento $documento) => [$documento->id => $secciones->extraer((string) $documento->contenido_extraido)['texto']])
            ->filter()
            ->toArray();

        $servicio = new SimilitudService;
        $vectores = $servicio->calcularVectoresTfIdf($corpus);

        $porcentaje = $servicio->similitudCoseno(
            $vectores[$proyectoA->documento->id] ?? [],
            $vectores[$proyectoB->documento->id] ?? []
        );
        $explicacion = $servicio->explicarSimilitud(
            $vectores[$proyectoA->documento->id] ?? [],
            $vectores[$proyectoB->documento->id] ?? []
        );
        $coincidencias = $servicio->encontrarCoincidencias(
            $contenidoA['texto'],
            $contenidoB['texto']
        );

        $proyectoA->loadMissing(['estudiante', 'carrera']);
        $proyectoB->loadMissing(['estudiante', 'carrera']);

        return view('analisis.index', compact('proyectoA', 'proyectoB', 'porcentaje', 'coincidencias', 'explicacion'));
    }

    private function proyectoSeleccionable(mixed $id, SeccionesDocumentoService $secciones): ?ProyectoTitulacion
    {
        if (! is_numeric($id)) {
            return null;
        }

        $proyecto = ProyectoTitulacion::where('activo', true)
            ->whereHas('documento', fn ($query) => $query->whereNotNull('contenido_extraido')->where('contenido_extraido', '!=', ''))
            ->with(['estudiante', 'carrera'])
            ->find((int) $id);

        return $proyecto && $secciones->tieneContenidoAnalizable((string) $proyecto->documento?->contenido_extraido)
            ? $proyecto
            : null;
    }
}
