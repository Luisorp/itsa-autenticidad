<x-app-layout>
    <x-slot name="header">
        <div>
            <h2>Resultados de similitud: {{ $proyecto->titulo }}</h2>
            <span class="result-modality"><i class="bi bi-mortarboard"></i> {{ $proyecto->modalidad_nombre }}</span>
        </div>
    </x-slot>

    <div class="container py-4">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="GET" class="row g-2 mb-3 align-items-end" style="max-width: 500px;">
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

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Proyecto comparado</th>
                    <th>Modalidad</th>
                    <th>Estudiante</th>
                    <th>% Similitud</th>
                </tr>
            </thead>
            <tbody>
                @forelse($comparaciones as $comp)
                    @php
                        $otro = $comp->documento_a_id === $documento->id ? $comp->documentoB : $comp->documentoA;
                        $porcentaje = $comp->porcentaje_similitud;
                        $clase = $porcentaje >= 70 ? 'bg-danger' : ($porcentaje >= 40 ? 'bg-warning text-dark' : 'bg-secondary');
                    @endphp
                    <tr>
                        <td>{{ $otro->proyecto->titulo }}</td>
                        <td><span class="modality-badge">{{ $otro->proyecto->modalidad_nombre }}</span></td>
                        <td>{{ $otro->proyecto->estudiante->name }}</td>
                        <td><span class="badge {{ $clase }}">{{ $porcentaje }}%</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center">No hay otros documentos con los cuales comparar todavía.</td></tr>
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
