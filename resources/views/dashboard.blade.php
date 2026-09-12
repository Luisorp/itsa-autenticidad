<x-app-layout>
    <x-slot name="header">
        <div class="page-title"><h2>Dashboard</h2><span>Resumen general del sistema</span></div>
    </x-slot>

    <div class="dashboard-shell">
        <section class="dashboard-hero">
            <div class="dashboard-hero-icon"><i class="bi bi-mortarboard-fill"></i></div>
            <div class="dashboard-hero-copy">
                <span class="section-kicker">Sello ITSa</span>
                <h1>Bienvenido, {{ Str::before(Auth::user()->name, ' ') }}</h1>
                <p>Sistema de análisis de autenticidad de proyectos de titulación</p>
            </div>
            <blockquote>“Conocimiento íntegro<br>para un mejor futuro”</blockquote>
            <img src="{{ asset('images/edificio-itsa.png') }}" alt="" class="dashboard-building">
        </section>

        <section class="dashboard-metrics" aria-label="Indicadores principales">
            <article class="metric-card metric-green"><span class="metric-icon"><i class="bi bi-folder-fill"></i></span><div><strong>{{ $totalProyectos }}</strong><span>Proyectos registrados</span><small><i class="bi bi-database-check"></i> Repositorio activo</small></div></article>
            <article class="metric-card metric-amber"><span class="metric-icon"><i class="bi bi-file-earmark-text-fill"></i></span><div><strong>{{ $totalPendientes }}</strong><span>Pendientes de análisis</span><small><i class="bi bi-clock"></i> Requieren revisión</small></div></article>
            <article class="metric-card metric-green"><span class="metric-icon"><i class="bi bi-check-circle-fill"></i></span><div><strong>{{ $totalAnalizados }}</strong><span>Proyectos analizados</span><small><i class="bi bi-shield-check"></i> Proceso completado</small></div></article>
            <article class="metric-card metric-mint"><span class="metric-icon"><i class="bi bi-percent"></i></span><div><strong>{{ $similitudPromedio }}%</strong><span>Similitud promedio</span><small><i class="bi bi-graph-up"></i> Comparaciones activas</small></div></article>
        </section>

        @if(Auth::user()->esAdministrador() || Auth::user()->esDocente())
            <section class="dashboard-actions" aria-label="Acciones rápidas">
                <a class="primary" href="{{ route('proyectos.create') }}"><i class="bi bi-plus-lg"></i> Nuevo proyecto</a>
                <a href="{{ route('analisis.index') }}"><i class="bi bi-intersect"></i> Comparar proyectos</a>
                <a href="{{ route('reportes.index') }}"><i class="bi bi-bar-chart-fill"></i> Ver reportes</a>
                @if(Auth::user()->esAdministrador())<a href="{{ route('respaldos.index') }}"><i class="bi bi-cloud-arrow-down"></i> Copias de seguridad</a>@endif
            </section>
        @endif

        <div class="dashboard-grid">
            <section class="dashboard-panel recent-projects-panel">
                <header><div><span class="section-kicker">Actividad académica</span><h3>Proyectos recientes</h3></div><a href="{{ route('proyectos.index') }}">Ver todos <i class="bi bi-arrow-right"></i></a></header>
                <div class="table-responsive"><table class="table dashboard-table">
                    <thead><tr><th>#</th><th>Título del proyecto</th><th>Similitud</th><th>Estado</th><th>Fecha</th></tr></thead>
                    <tbody>
                        @forelse($proyectosRecientes as $indice => $proyecto)
                            @php $nivel = $proyecto->porcentaje === null ? 'neutral' : ($proyecto->porcentaje > 75 ? 'high' : ($proyecto->porcentaje > 25 ? 'medium' : 'low')); @endphp
                            <tr>
                                <td>{{ $indice + 1 }}</td><td><strong>{{ $proyecto->titulo }}</strong></td>
                                <td>@if($proyecto->porcentaje !== null)<span class="similarity-pill similarity-{{ $nivel }}">{{ number_format($proyecto->porcentaje, 2) }}%</span>@else<span class="similarity-pill similarity-neutral">—</span>@endif</td>
                                <td>@if($proyecto->estado === 'analizado')<span class="status-pill status-ready">Analizado</span>@else<span class="status-pill status-pending">Pendiente</span>@endif</td>
                                <td class="text-nowrap">{{ $proyecto->fecha }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No hay proyectos registrados todavía.</td></tr>
                        @endforelse
                    </tbody>
                </table></div>
            </section>

            <section class="dashboard-panel similarity-panel">
                <header><div><span class="section-kicker">Indicador global</span><h3>Resumen de similitud</h3></div></header>
                <div class="similarity-summary">
                    <div class="dashboard-donut" style="--valor: {{ min($similitudPromedio, 100) }}"><div><strong>{{ $similitudPromedio }}%</strong><span>Promedio</span></div></div>
                    <ul>
                        <li><i class="low"></i><span>Baja · 0–25%</span><strong>{{ $rangosSimilitud['baja'] }}</strong></li>
                        <li><i class="medium"></i><span>Media · 26–75%</span><strong>{{ $rangosSimilitud['media'] }}</strong></li>
                        <li><i class="high"></i><span>Alta · 76–100%</span><strong>{{ $rangosSimilitud['alta'] }}</strong></li>
                    </ul>
                </div>
                <footer>Basado en {{ array_sum($rangosSimilitud) }} comparaciones activas <a href="{{ route('reportes.index') }}">Ver detalles <i class="bi bi-arrow-right"></i></a></footer>
            </section>

            <section class="dashboard-panel repository-panel">
                <header><div><span class="section-kicker">Estado institucional</span><h3>Repositorio académico</h3></div></header>
                <div class="repository-content"><span><i class="bi bi-bank"></i></span><div><strong>{{ $totalCarreras }} carreras activas</strong><p>Los proyectos se organizan por carrera y modalidad para mantener comparaciones pertinentes.</p></div></div>
            </section>

            <section class="dashboard-panel shortcuts-panel">
                <header><div><span class="section-kicker">Navegación</span><h3>Atajos útiles</h3></div></header>
                <div class="shortcut-grid">
                    <a href="{{ route('proyectos.index') }}"><i class="bi bi-folder2-open"></i><span>Explorar proyectos</span></a>
                    <a href="{{ route('analisis.index') }}"><i class="bi bi-intersect"></i><span>Comparar proyectos</span></a>
                    @if(Auth::user()->esAdministrador())<a href="{{ route('usuarios.index') }}"><i class="bi bi-people"></i><span>Gestionar usuarios</span></a>@endif
                    <a href="{{ route('crossref.index') }}"><i class="bi bi-search"></i><span>Búsqueda académica</span></a>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
