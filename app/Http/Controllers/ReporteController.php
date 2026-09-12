<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use Illuminate\Support\Facades\Storage;

class ReporteController extends Controller
{
    public function index()
    {
        $query = Reporte::with(['proyecto', 'generadoPor'])->latest();

        if (auth()->user()->esEstudiante()) {
            $query->whereHas('proyecto', fn ($proyecto) => $proyecto->where('estudiante_id', auth()->id()));
        }

        $reportes = $query->paginate(10);

        return view('reportes.index', compact('reportes'));
    }

    public function verArchivo(Reporte $reporte)
    {
        $reporte->loadMissing('proyecto');
        abort_if(
            auth()->user()->esEstudiante() && $reporte->proyecto?->estudiante_id !== auth()->id(),
            403,
            'No tienes permiso para consultar este reporte.'
        );

        abort_unless(Storage::disk('public')->exists($reporte->ruta_pdf), 404, 'El archivo del reporte no existe en el almacenamiento.');

        return Storage::disk('public')->response(
            $reporte->ruta_pdf,
            basename($reporte->ruta_pdf),
            ['Content-Type' => 'application/pdf'],
            'inline'
        );
    }
}
