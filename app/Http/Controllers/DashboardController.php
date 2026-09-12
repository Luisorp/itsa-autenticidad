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
        $totalProyectos = ProyectoTitulacion::where('activo', true)->count();
        $totalAnalizados = ProyectoTitulacion::where('activo', true)->where('estado', 'analizado')->count();
        $totalPendientes = ProyectoTitulacion::where('activo', true)->where('estado', 'pendiente_analisis')->count();

        $comparacionesActivas = Comparacion::whereHas('documentoA.proyecto', fn ($query) => $query->where('activo', true))
            ->whereHas('documentoB.proyecto', fn ($query) => $query->where('activo', true));
        $similitudPromedio = round((clone $comparacionesActivas)->avg('porcentaje_similitud') ?? 0);
        $porcentajes = (clone $comparacionesActivas)->pluck('porcentaje_similitud');
        $rangosSimilitud = [
            'baja' => $porcentajes->filter(fn ($valor) => $valor <= 25)->count(),
            'media' => $porcentajes->filter(fn ($valor) => $valor > 25 && $valor <= 75)->count(),
            'alta' => $porcentajes->filter(fn ($valor) => $valor > 75)->count(),
        ];

        $proyectosRecientes = ProyectoTitulacion::where('activo', true)->with('documento')
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
