<x-app-layout>
    <x-slot name="header">
        <div>
            <h2>Resultados de similitud: {{ $proyecto->titulo }}</h2>
            <span class="result-modality"><i class="bi bi-mortarboard"></i> {{ $proyecto->modalidad_nombre }}</span>
        </div>
    </x-slot>

    <div class="container py-4">
        @if(session('success'))
            <x-flash-success :message="session('success')" />
        @endif

        <div class="similarity-notice"><i class="bi bi-info-circle"></i><span>Los nuevos análisis usan todo el texto extraído del PDF. Si este resultado se obtuvo por secciones, vuelve a analizar el proyecto para actualizarlo.</span></div>

        <form method="GET" class="row g-2 mb-3 align-items-end" style="max-width: 500px;">
            @if($mostrarTodos)<input type="hidden" name="todos" value="1">@endif
            <div class="col-auto">
                <label class="form-label">Mínimo %</label>
                <input type="number" name="min" class="form-control" min="0" max="100" step="0.01" value="{{ request('min') }}">
            </div>
            <div class="col-auto">
                <label class="form-label">Máximo %</label>
                <input type="number" name="max" class="form-control" min="0" max="100" step="0.01" value="{{ request('max') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-primary">Filtrar</button>
                <a href="{{ route('proyectos.resultados', $proyecto) }}" class="btn btn-outline-secondary">Limpiar</a>
            </div>
        </form>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <p class="mb-0">Mostrando {{ $comparaciones->count() }} de {{ $totalComparaciones }} resultados, de mayor a menor similitud.</p>
            @if($totalComparaciones > 10 || $mostrarTodos)
                <a class="btn btn-outline-primary" href="{{ route('proyectos.resultados', array_merge(['proyecto' => $proyecto->id], request()->only(['min', 'max']), ['todos' => $mostrarTodos ? 0 : 1])) }}">
                    {{ $mostrarTodos ? 'Ver solo los 10 más altos' : 'Ver todos' }}
                </a>
            @endif
        </div>
        <table role="table" class="table mobile-record-table table-bordered">
            <thead role="rowgroup">
                <tr role="row">
                    <th scope="col" role="columnheader">Proyecto comparado</th>
                    <th scope="col" role="columnheader">Modalidad</th>
                    <th scope="col" role="columnheader">Estudiante</th>
                    <th scope="col" role="columnheader">% Similitud</th>
                </tr>
            </thead>
            <tbody role="rowgroup">
                @forelse($comparaciones as $comp)
                    @php
                        $otro = $comp->documento_a_id === $documento->id ? $comp->documentoB : $comp->documentoA;
                        $porcentaje = $comp->porcentaje_similitud;
                        $clase = $porcentaje >= 70 ? 'bg-danger' : ($porcentaje >= 40 ? 'bg-warning text-dark' : 'bg-secondary');
                    @endphp
                    <tr role="row">
                        <td role="cell" data-label="Proyecto comparado">{{ $otro->proyecto->titulo }}</td>
                        <td role="cell" data-label="Modalidad"><span class="modality-badge">{{ $otro->proyecto->modalidad_nombre }}</span></td>
                        <td role="cell" data-label="Estudiante">{{ $otro->proyecto->estudiante->nombre }}</td>
                        <td role="cell" data-label="% Similitud"><span class="badge {{ $clase }}">{{ $porcentaje }}%</span></td>
                    </tr>
                @empty
                    <tr role="row"><td role="cell" colspan="4" class="text-center">No hay otros documentos con los cuales comparar todavía.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="d-flex flex-wrap gap-2 mt-3">
            <a href="{{ route('analisis.index', ['proyecto_a' => $proyecto->id]) }}" class="btn btn-primary">
                <i class="bi bi-intersect me-1"></i> Comparar con otro proyecto
            </a>
            <a href="{{ route('proyectos.reporte', $proyecto) }}" class="btn btn-success">
                <i class="bi bi-file-earmark-pdf me-1"></i> Descargar reporte PDF
            </a>
            <a href="{{ route('proyectos.index') }}" class="btn btn-secondary">Volver</a>
        </div>
        
    </div>
</x-app-layout>
