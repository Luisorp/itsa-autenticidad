<?php

// seccion de analisi de dos proyectos

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\ProyectoTitulacion;
use App\Services\SimilitudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AnalisisController extends Controller
{
    public function index(Request $request)
    {
        $proyectoA = $this->proyectoSeleccionable($request->old('proyecto_a', $request->input('proyecto_a')));
        $proyectoB = $this->proyectoSeleccionable($request->old('proyecto_b'));

        return view('analisis.index', compact('proyectoA', 'proyectoB'));
    }

    public function buscarProyectos(Request $request): JsonResponse
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
            ->with(['estudiante:id,name', 'carrera:id,nombre,codigo']);

        if ($termino !== '') {
            $query->where(function ($q) use ($termino) {
                $q->where('titulo', 'like', "%{$termino}%")
                    ->orWhereHas('estudiante', fn ($estudiante) => $estudiante->where('name', 'like', "%{$termino}%"))
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

        $proyectos = $query->orderBy('titulo')->limit(15)->get()->map(fn ($proyecto) => [
            'id' => $proyecto->id,
            'titulo' => $proyecto->titulo,
            'modalidad' => $proyecto->modalidad,
            'modalidad_nombre' => $proyecto->modalidad_nombre,
            'estudiante' => $proyecto->estudiante?->name ?? 'Sin estudiante',
            'carrera' => $proyecto->carrera?->codigo ?? $proyecto->carrera?->nombre ?? 'Sin carrera',
            'anio' => $proyecto->anio,
        ]);

        return response()->json(['data' => $proyectos]);
    }

    public function comparar(Request $request)
    {
        $validated = $request->validate([
            'proyecto_a' => 'required|exists:proyectos_titulacion,id|different:proyecto_b',
            'proyecto_b' => 'required|exists:proyectos_titulacion,id',
        ]);

        $proyectoA = ProyectoTitulacion::where('activo', true)->with('documento')->findOrFail($validated['proyecto_a']);
        $proyectoB = ProyectoTitulacion::where('activo', true)->with('documento')->findOrFail($validated['proyecto_b']);

        $errores = [];
        if (! $proyectoA->documento || trim((string) $proyectoA->documento->contenido_extraido) === '') {
            $errores['proyecto_a'] = 'El Proyecto A no tiene texto disponible para comparar.';
        }
        if (! $proyectoB->documento || trim((string) $proyectoB->documento->contenido_extraido) === '') {
            $errores['proyecto_b'] = 'El Proyecto B no tiene texto disponible para comparar.';
        }
        if ($proyectoA->modalidad !== $proyectoB->modalidad) {
            $errores['proyecto_b'] = 'Solo se pueden comparar proyectos de la misma modalidad de graduación.';
        }
        if ($errores !== []) {
            throw ValidationException::withMessages($errores);
        }

        $corpus = Documento::whereNotNull('contenido_extraido')
            ->whereHas('proyecto', fn ($query) => $query->where('modalidad', $proyectoA->modalidad)->where('activo', true))
            ->pluck('contenido_extraido', 'id')
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
            $proyectoA->documento->contenido_extraido,
            $proyectoB->documento->contenido_extraido
        );

        $proyectoA->loadMissing(['estudiante', 'carrera']);
        $proyectoB->loadMissing(['estudiante', 'carrera']);

        return view('analisis.index', compact('proyectoA', 'proyectoB', 'porcentaje', 'coincidencias', 'explicacion'));
    }

    private function proyectoSeleccionable(mixed $id): ?ProyectoTitulacion
    {
        if (! is_numeric($id)) {
            return null;
        }

        return ProyectoTitulacion::where('activo', true)
            ->whereHas('documento', fn ($query) => $query->whereNotNull('contenido_extraido')->where('contenido_extraido', '!=', ''))
            ->with(['estudiante', 'carrera'])
            ->find((int) $id);
    }
}
