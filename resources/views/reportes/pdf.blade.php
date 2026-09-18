<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Helvetica, sans-serif; font-size: 12px; color: #222; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        h2 { font-size: 14px; margin-top: 24px; margin-bottom: 8px; border-bottom: 1px solid #ccc; padding-bottom: 4px; }
        .meta { margin-bottom: 16px; }
        .meta td { padding: 2px 8px 2px 0; }
        table.resultados { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.resultados th, table.resultados td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; font-size: 11px; }
        table.resultados th { background-color: #f2f2f2; }
        .badge { padding: 2px 6px; border-radius: 3px; color: white; font-size: 10px; }
        .badge-alto { background-color: #dc3545; }
        .badge-medio { background-color: #ffc107; color: #333; }
        .badge-bajo { background-color: #6c757d; }
        .footer { margin-top: 30px; font-size: 10px; color: #666; border-top: 1px solid #ccc; padding-top: 8px; }
    </style>
</head>
<body>
    <h1>Reporte de análisis de autenticidad</h1>
    <p>Instituto Tecnológico Sacaba — Carrera de Sistemas Informáticos</p>

    <table class="meta">
        <tr><td><strong>Proyecto:</strong></td><td>{{ $proyecto->titulo }}</td></tr>
        <tr><td><strong>Modalidad:</strong></td><td>{{ $proyecto->modalidad_nombre }}</td></tr>
        <tr><td><strong>Estudiante:</strong></td><td>{{ $proyecto->estudiante->nombre }}</td></tr>
        <tr><td><strong>Carrera:</strong></td><td>{{ $proyecto->carrera->nombre }}</td></tr>
        <tr><td><strong>Año:</strong></td><td>{{ $proyecto->anio }}</td></tr>
        <tr><td><strong>Docente tutor:</strong></td><td>{{ $proyecto->tutor->nombre ?? 'No asignado' }}</td></tr>
        <tr><td><strong>Fecha del reporte:</strong></td><td>{{ now()->format('d/m/Y H:i') }}</td></tr>
        <tr><td><strong>Generado por:</strong></td><td>{{ auth()->user()->name }}</td></tr>
    </table>

    <h2>Resultados de comparación</h2>

    <table class="resultados">
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
                    $clase = $porcentaje >= 70 ? 'badge-alto' : ($porcentaje >= 40 ? 'badge-medio' : 'badge-bajo');
                @endphp
                <tr>
                    <td>{{ $otro->proyecto->titulo }}</td>
                    <td>{{ $otro->proyecto->modalidad_nombre }}</td>
                    <td>{{ $otro->proyecto->estudiante->nombre }}</td>
                    <td><span class="badge {{ $clase }}">{{ $porcentaje }}%</span></td>
                </tr>
            @empty
                <tr><td colspan="4">No hay comparaciones registradas todavía.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Este reporte fue generado automáticamente por el sistema de análisis de autenticidad. El porcentaje de similitud es un indicador de apoyo basado en coincidencia textual (algoritmo TF-IDF + similitud de coseno); la decisión final sobre la autenticidad del trabajo corresponde al tribunal evaluador.
    </div>
</body>
</html>
