<x-app-layout>
    <x-slot name="header"><div class="page-title"><h2>Reportes generados</h2><span>{{ Auth::user()->esUsuario() ? 'Resultados de tus proyectos' : 'Historial institucional de reportes' }}</span></div></x-slot>

    <div class="container-fluid">
        <table role="table" class="table mobile-record-table table-bordered">
            <thead role="rowgroup">
                <tr role="row">
                    <th scope="col" role="columnheader">Proyecto</th>
                    <th scope="col" role="columnheader">Generado por</th>
                    <th scope="col" role="columnheader">Fecha</th>
                    <th scope="col" role="columnheader">Descarga</th>
                </tr>
            </thead>
            <tbody role="rowgroup">
                @forelse($reportes as $reporte)
                    <tr role="row">
                        <td role="cell" data-label="Proyecto">{{ $reporte->proyecto->titulo }}</td>
                        <td role="cell" data-label="Generado por">{{ $reporte->generadoPor->name }}</td>
                        <td role="cell" data-label="Fecha">{{ $reporte->updated_at->format('d/m/Y H:i') }}</td>
                        <td role="cell" data-label="Descarga">
                            @if($reporte->archivo_disponible)
                                <a href="{{ route('reportes.archivo', $reporte) }}" target="_blank" rel="noopener"><i class="bi bi-file-earmark-pdf me-1"></i>Ver PDF</a>
                            @else
                                <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>PDF no disponible</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr role="row"><td role="cell" colspan="4" class="text-center">Todavía no se ha generado ningún reporte.</td></tr>
                @endforelse
            </tbody>
        </table>

        {{ $reportes->links() }}
    </div>
</x-app-layout>
