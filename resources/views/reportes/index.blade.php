<x-app-layout>
    <x-slot name="header"><div class="page-title"><h2>Reportes generados</h2><span>{{ Auth::user()->esEstudiante() ? 'Resultados de tus proyectos' : 'Historial institucional de reportes' }}</span></div></x-slot>

    <div class="container-fluid">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Proyecto</th>
                    <th>Generado por</th>
                    <th>Fecha</th>
                    <th>Descarga</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportes as $reporte)
                    <tr>
                        <td>{{ $reporte->proyecto->titulo }}</td>
                        <td>{{ $reporte->generadoPor->name }}</td>
                        <td>{{ $reporte->updated_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @if($reporte->archivo_disponible)
                                <a href="{{ route('reportes.archivo', $reporte) }}" target="_blank" rel="noopener"><i class="bi bi-file-earmark-pdf me-1"></i>Ver PDF</a>
                            @else
                                <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>PDF no disponible</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center">Todavía no se ha generado ningún reporte.</td></tr>
                @endforelse
            </tbody>
        </table>

        {{ $reportes->links() }}
    </div>
</x-app-layout>
