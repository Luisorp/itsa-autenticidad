<?php

namespace App\Http\Controllers;

use App\Models\Carrera;
use App\Models\Comparacion;
use App\Models\ProyectoTitulacion;

class DashboardController extends Controller
{
    public function index()
    {
        $totalCarreras = Carrera::where('activo', true)->count();
        $proyectosVisibles = ProyectoTitulacion::where('activo', true);
        if (auth()->user()->esUsuario()) {
            $proyectosVisibles->whereHas('estudiante', fn ($estudiante) => $estudiante->where('user_id', auth()->id()));
        }

        $totalProyectos = (clone $proyectosVisibles)->count();
        $totalAnalizados = (clone $proyectosVisibles)->where('estado', 'analizado')->count();
        $totalPendientes = (clone $proyectosVisibles)->where('estado', 'pendiente_analisis')->count();

        $comparacionesActivas = Comparacion::whereHas('documentoA.proyecto', fn ($query) => $query->where('activo', true))
            ->whereHas('documentoB.proyecto', fn ($query) => $query->where('activo', true));
        if (auth()->user()->esUsuario()) {
            $comparacionesActivas->where(function ($query) {
                $query->whereHas('documentoA.proyecto.estudiante', fn ($estudiante) => $estudiante->where('user_id', auth()->id()))
                    ->orWhereHas('documentoB.proyecto.estudiante', fn ($estudiante) => $estudiante->where('user_id', auth()->id()));
            });
        }
        $similitudPromedio = round((clone $comparacionesActivas)->avg('porcentaje_similitud') ?? 0);
        $porcentajes = (clone $comparacionesActivas)->pluck('porcentaje_similitud');
        $rangosSimilitud = [
            'baja' => $porcentajes->filter(fn ($valor) => $valor <= 25)->count(),
            'media' => $porcentajes->filter(fn ($valor) => $valor > 25 && $valor <= 75)->count(),
            'alta' => $porcentajes->filter(fn ($valor) => $valor > 75)->count(),
        ];

        $proyectosRecientes = (clone $proyectosVisibles)->with('documento')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($proyecto) {
                $documentoId = $proyecto->documento?->id;

                $mejorComparacion = $documentoId
                    ? Comparacion::where(function ($query) use ($documentoId) {
                        $query->where('documento_a_id', $documentoId)
                            ->orWhere('documento_b_id', $documentoId);
                    })
                        ->whereHas('documentoA.proyecto', fn ($query) => $query->where('activo', true))
                        ->whereHas('documentoB.proyecto', fn ($query) => $query->where('activo', true))
                        ->orderByDesc('porcentaje_similitud')
                        ->first()
                    : null;

                return (object) [
                    'titulo' => $proyecto->titulo,
                    'porcentaje' => $mejorComparacion->porcentaje_similitud ?? null,
                    'estado' => $proyecto->estado,
                    'fecha' => $proyecto->created_at->format('d/m/Y'),
                ];
            });

        return view('dashboard', compact(
            'totalCarreras',
            'totalProyectos',
            'totalAnalizados',
            'totalPendientes',
            'similitudPromedio',
            'rangosSimilitud',
            'proyectosRecientes'
        ));
    }
}
