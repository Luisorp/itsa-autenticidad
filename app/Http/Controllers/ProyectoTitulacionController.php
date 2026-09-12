<?php

namespace App\Http\Controllers;

use App\Models\Carrera;
use App\Models\Comparacion;
use App\Models\Documento;
use App\Models\ProyectoTitulacion;
use App\Models\Reporte;
use App\Models\User;
use App\Services\SimilitudService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
// ruta de reportes
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Smalot\PdfParser\Parser;

class ProyectoTitulacionController extends Controller
{
    public function index(Request $request)
    {
        $carreras = Carrera::where('activo', true)->orderBy('nombre')->get();
        $anios = ProyectoTitulacion::select('anio')->distinct()->orderByDesc('anio')->pluck('anio');
        $modalidades = ProyectoTitulacion::MODALIDADES;

        $vista = $request->string('vista')->toString() === 'archivados' ? 'archivados' : 'activos';
        $estado = in_array($request->string('estado')->toString(), ['pendiente_analisis', 'analizado'], true)
            ? $request->string('estado')->toString()
            : null;
        $orden = in_array($request->string('orden')->toString(), ['recientes', 'antiguos', 'titulo_asc', 'titulo_desc'], true)
            ? $request->string('orden')->toString()
            : 'recientes';

        $estadisticas = [
            'activos' => ProyectoTitulacion::where('activo', true)->count(),
            'pendientes' => ProyectoTitulacion::where('activo', true)->where('estado', 'pendiente_analisis')->count(),
            'analizados' => ProyectoTitulacion::where('activo', true)->where('estado', 'analizado')->count(),
            'archivados' => ProyectoTitulacion::where('activo', false)->count(),
        ];

        $query = ProyectoTitulacion::with(['carrera', 'estudiante', 'documento'])
            ->where('activo', $vista === 'activos');

        if ($request->filled('carrera_id')) {
            $query->where('carrera_id', $request->carrera_id);
        }

        if ($request->filled('anio')) {
            $query->where('anio', $request->anio);
        }

        if ($request->filled('modalidad')) {
            $query->where('modalidad', $request->modalidad);
        }

        if ($estado) {
            $query->where('estado', $estado);
        }

        if ($request->filled('buscar')) {
            $termino = $request->buscar;
            $query->where(function ($q) use ($termino) {
                $q->where('titulo', 'like', "%{$termino}%")
                    ->orWhereHas('estudiante', function ($q2) use ($termino) {
                        $q2->where('name', 'like', "%{$termino}%");
                    });
            });
        }

        match ($orden) {
            'antiguos' => $query->orderBy('anio')->orderBy('titulo'),
            'titulo_asc' => $query->orderBy('titulo'),
            'titulo_desc' => $query->orderByDesc('titulo'),
            default => $query->orderByDesc('anio')->orderByDesc('id'),
        };

        $proyectos = $query->paginate(10)->withQueryString();

        return view('proyectos.index', compact(
            'proyectos', 'carreras', 'anios', 'modalidades', 'vista', 'estado', 'orden', 'estadisticas'
        ));
    }

    public function create()
    {
        $carreras = Carrera::where('activo', true)->orderBy('nombre')->get();
        $estudiantes = User::where('rol', 'estudiante')->where('activo', true)->orderBy('name')->get();
        $tutores = User::where('rol', 'docente')->where('activo', true)->orderBy('name')->get();
        $modalidades = ProyectoTitulacion::MODALIDADES;

        return view('proyectos.create', compact('carreras', 'estudiantes', 'tutores', 'modalidades'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'resumen' => 'nullable|string',
            'modalidad' => ['required', Rule::in(array_keys(ProyectoTitulacion::MODALIDADES))],
            'carrera_id' => ['required', Rule::exists('carreras', 'id')->where(fn ($q) => $q->where('activo', true))],
            'estudiante_id' => ['required', Rule::exists('users', 'id')->where(fn ($q) => $q->where('rol', 'estudiante')->where('activo', true))],
            'tutor_id' => ['nullable', Rule::exists('users', 'id')->where(fn ($q) => $q->where('rol', 'docente')->where('activo', true))],
            'anio' => 'required|integer|min:2000|max:'.(date('Y') + 1),
            'documento' => 'required|file|mimes:pdf|max:30720',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $proyecto = ProyectoTitulacion::create([
                'titulo' => $validated['titulo'],
                'resumen' => $validated['resumen'] ?? null,
                'modalidad' => $validated['modalidad'],
                'carrera_id' => $validated['carrera_id'],
                'estudiante_id' => $validated['estudiante_id'],
                'tutor_id' => $validated['tutor_id'] ?? null,
                'anio' => $validated['anio'],
                'estado' => 'pendiente_analisis',
            ]);

            $archivo = $request->file('documento');
            $ruta = $archivo->store('documentos', 'public');

            $texto = null;
            try {
                $parser = new Parser;
                $pdf = $parser->parseFile(storage_path('app/public/'.$ruta));
                $texto = $pdf->getText();
            } catch (\Exception $e) {
                // Si el PDF está escaneado (solo imágenes) o corrupto, seguimos sin texto por ahora.
                $texto = null;
            }

            Documento::create([
                'proyecto_id' => $proyecto->id,
                'nombre_archivo' => $archivo->getClientOriginalName(),
                'ruta_archivo' => $ruta,
                'tipo_archivo' => $archivo->getClientOriginalExtension(),
                'tamano_archivo' => $archivo->getSize(),
                'hash_archivo' => hash_file('sha256', $archivo->getRealPath()),
                'contenido_extraido' => $texto,
            ]);
        });

        return redirect()->route('proyectos.index')->with('success', 'Proyecto registrado correctamente.');
    }

    public function edit(ProyectoTitulacion $proyecto)
    {
        $carreras = Carrera::where(function ($query) use ($proyecto) {
            $query->where('activo', true)->orWhere('id', $proyecto->carrera_id);
        })->orderBy('nombre')->get();
        $estudiantes = User::where('rol', 'estudiante')->where(function ($query) use ($proyecto) {
            $query->where('activo', true)->orWhere('id', $proyecto->estudiante_id);
        })->orderBy('name')->get();
        $tutores = User::where('rol', 'docente')->where(function ($query) use ($proyecto) {
            $query->where('activo', true)->orWhere('id', $proyecto->tutor_id);
        })->orderBy('name')->get();
        $modalidades = ProyectoTitulacion::MODALIDADES;

        return view('proyectos.edit', compact('proyecto', 'carreras', 'estudiantes', 'tutores', 'modalidades'));
    }

    public function update(Request $request, ProyectoTitulacion $proyecto)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'resumen' => 'nullable|string',
            'modalidad' => ['required', Rule::in(array_keys(ProyectoTitulacion::MODALIDADES))],
            'carrera_id' => 'required|exists:carreras,id',
            'estudiante_id' => ['required', Rule::exists('users', 'id')->where(fn ($q) => $q->where('rol', 'estudiante'))],
            'tutor_id' => ['nullable', Rule::exists('users', 'id')->where(fn ($q) => $q->where('rol', 'docente'))],
            'anio' => 'required|integer|min:2000|max:'.(date('Y') + 1),
            'documento' => 'nullable|file|mimes:pdf|max:30720',
        ]);

        $archivo = $request->file('documento');
        unset($validated['documento']);

        if (! $archivo) {
            $proyecto->update($validated);

            return redirect()->route('proyectos.index')->with('success', 'Proyecto actualizado correctamente.');
        }

        $rutaNueva = $archivo->store('documentos', 'public');
        $rutaAnterior = $proyecto->documento?->ruta_archivo;
        $reportesAnteriores = $proyecto->reportes()->pluck('ruta_pdf')->all();

        try {
            $texto = null;
            try {
                $texto = (new Parser)->parseFile(storage_path('app/public/'.$rutaNueva))->getText();
            } catch (\Exception) {
                // El archivo se conserva aunque sea un PDF escaneado sin texto extraíble.
            }

            DB::transaction(function () use ($proyecto, $validated, $archivo, $rutaNueva, $texto) {
                $documentoId = $proyecto->documento?->id;
                if ($documentoId) {
                    Comparacion::where('documento_a_id', $documentoId)
                        ->orWhere('documento_b_id', $documentoId)
                        ->delete();
                }

                $proyecto->reportes()->delete();
                $proyecto->update([...$validated, 'estado' => 'pendiente_analisis']);
                $proyecto->documento()->updateOrCreate([], [
                    'nombre_archivo' => $archivo->getClientOriginalName(),
                    'ruta_archivo' => $rutaNueva,
                    'tipo_archivo' => $archivo->getClientOriginalExtension(),
                    'tamano_archivo' => $archivo->getSize(),
                    'hash_archivo' => hash_file('sha256', $archivo->getRealPath()),
                    'contenido_extraido' => $texto,
                ]);
            });
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($rutaNueva);
            throw $exception;
        }

        if ($rutaAnterior && $rutaAnterior !== $rutaNueva) {
            Storage::disk('public')->delete($rutaAnterior);
        }
        Storage::disk('public')->delete($reportesAnteriores);

        return redirect()->route('proyectos.index')->with('success', 'Proyecto y documento actualizados. Los análisis anteriores fueron invalidados y deben ejecutarse nuevamente.');
    }

    public function toggleArchivado(ProyectoTitulacion $proyecto)
    {
        $proyecto->update(['activo' => ! $proyecto->activo]);

        return redirect()
            ->route('proyectos.index', ['vista' => $proyecto->activo ? 'activos' : 'archivados'])
            ->with('success', $proyecto->activo
                ? 'Proyecto restaurado y disponible nuevamente.'
                : 'Proyecto archivado. Sus documentos, análisis y reportes se conservaron.');
    }

    // analizis-----------------

    public function analizar(ProyectoTitulacion $proyecto)
    {
        if (! $proyecto->activo) {
            return redirect()->route('proyectos.index')->with('error', 'No se puede analizar un proyecto archivado. Restáuralo primero.');
        }

        $documento = $proyecto->documento;

        if (! $documento || trim((string) $documento->contenido_extraido) === '') {
            return redirect()->route('proyectos.index')->with('error', 'Este proyecto no tiene un documento con texto extraído.');
        }

        $otrosDocumentos = Documento::where('id', '!=', $documento->id)
            ->whereNotNull('contenido_extraido')
            ->where('contenido_extraido', '!=', '')
            ->whereHas('proyecto', function ($query) use ($proyecto) {
                $query->where('carrera_id', $proyecto->carrera_id)
                    ->where('modalidad', $proyecto->modalidad)
                    ->where('activo', true);
            })
            ->get();

        if ($otrosDocumentos->isEmpty()) {
            $proyecto->update(['estado' => 'analizado']);

            return redirect()->route('proyectos.resultados', $proyecto)->with('success', 'Análisis completado (todavía no hay otros proyectos de la misma carrera y modalidad con los cuales comparar).');
        }

        $servicio = new SimilitudService;

        $corpus = [$documento->id => $documento->contenido_extraido];
        foreach ($otrosDocumentos as $otro) {
            $corpus[$otro->id] = $otro->contenido_extraido;
        }

        $vectores = $servicio->calcularVectoresTfIdf($corpus);

        foreach ($otrosDocumentos as $otro) {
            $porcentaje = $servicio->similitudCoseno($vectores[$documento->id], $vectores[$otro->id]);

            $idA = min($documento->id, $otro->id);
            $idB = max($documento->id, $otro->id);

            Comparacion::updateOrCreate(
                ['documento_a_id' => $idA, 'documento_b_id' => $idB],
                ['porcentaje_similitud' => $porcentaje, 'algoritmo_usado' => 'TF-IDF + similitud de coseno']
            );
        }

        $proyecto->update(['estado' => 'analizado']);

        return redirect()->route('proyectos.resultados', $proyecto)->with('success', 'Análisis completado.');
    }

    public function resultados(Request $request, ProyectoTitulacion $proyecto)
    {
        $documento = $proyecto->documento;

        if (! $documento) {
            return redirect()->route('proyectos.index')->with('error', 'Este proyecto no tiene documento asociado.');
        }

        $request->validate([
            'min' => ['nullable', 'numeric', 'between:0,100'],
            'max' => ['nullable', 'numeric', 'between:0,100', 'gte:min'],
        ]);

        $query = $this->comparacionesDeLaMismaModalidad($documento, $proyecto->modalidad)
            ->with(['documentoA.proyecto.estudiante', 'documentoB.proyecto.estudiante']);

        if ($request->filled('min')) {
            $query->where('porcentaje_similitud', '>=', $request->min);
        }

        if ($request->filled('max')) {
            $query->where('porcentaje_similitud', '<=', $request->max);
        }

        $comparaciones = $query->orderByDesc('porcentaje_similitud')->get();

        return view('proyectos.resultados', compact('proyecto', 'documento', 'comparaciones'));
    }

    public function verDocumento(ProyectoTitulacion $proyecto)
    {
        $documento = $proyecto->documento;

        abort_if(! $documento, 404, 'El proyecto no tiene un documento asociado.');
        abort_unless(Storage::disk('public')->exists($documento->ruta_archivo), 404, 'El archivo PDF no existe en el almacenamiento.');

        return Storage::disk('public')->response(
            $documento->ruta_archivo,
            $documento->nombre_archivo,
            ['Content-Type' => 'application/pdf'],
            'inline'
        );
    }

    // metodo reportes pdf
    public function reporte(ProyectoTitulacion $proyecto)
    {
        abort_if(
            auth()->user()->esEstudiante() && $proyecto->estudiante_id !== auth()->id(),
            403,
            'No tienes permiso para generar el reporte de este proyecto.'
        );

        $documento = $proyecto->documento;

        if (! $documento) {
            return redirect()->route('proyectos.index')->with('error', 'Este proyecto no tiene documento.');
        }

        $proyecto->load(['carrera', 'estudiante', 'tutor']);

        $comparaciones = $this->comparacionesDeLaMismaModalidad($documento, $proyecto->modalidad)
            ->with(['documentoA.proyecto.estudiante', 'documentoB.proyecto.estudiante'])
            ->orderByDesc('porcentaje_similitud')
            ->get();

        $pdf = Pdf::loadView('reportes.pdf', compact('proyecto', 'documento', 'comparaciones'));

        $nombreArchivo = 'reporte_'.Str::slug($proyecto->titulo).'.pdf';
        $ruta = 'reportes/'.$nombreArchivo;

        Storage::disk('public')->put($ruta, $pdf->output());

        Reporte::updateOrCreate(
            ['proyecto_id' => $proyecto->id],
            ['generado_por' => auth()->id(), 'ruta_pdf' => $ruta]
        );

        return $pdf->download($nombreArchivo);
    }

    private function comparacionesDeLaMismaModalidad(Documento $documento, string $modalidad)
    {
        return Comparacion::query()->where(function ($query) use ($documento, $modalidad) {
            $query->where(function ($comparacion) use ($documento, $modalidad) {
                $comparacion->where('documento_a_id', $documento->id)
                    ->whereHas('documentoB.proyecto', fn ($proyecto) => $proyecto->where('modalidad', $modalidad));
            })->orWhere(function ($comparacion) use ($documento, $modalidad) {
                $comparacion->where('documento_b_id', $documento->id)
                    ->whereHas('documentoA.proyecto', fn ($proyecto) => $proyecto->where('modalidad', $modalidad));
            });
        });
    }
}
