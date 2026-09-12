<x-app-layout>
    <x-slot name="header"><h2>Proyectos de grado</h2></x-slot>

    <div class="container py-4 project-catalog">
        @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
        @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

        <div class="catalog-heading">
            <div>
                <span class="section-kicker">Repositorio institucional</span>
                <h3>Gestión de proyectos</h3>
                <p>Consulta, analiza y conserva el historial académico desde un único lugar.</p>
            </div>
            @if(in_array(auth()->user()->rol, ['administrador', 'docente']))
                <a href="{{ route('proyectos.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Nuevo proyecto</a>
            @endif
        </div>

        <div class="project-stats">
            <div class="project-stat"><i class="bi bi-folder2-open"></i><span><strong>{{ $estadisticas['activos'] }}</strong> Disponibles</span></div>
            <div class="project-stat"><i class="bi bi-hourglass-split"></i><span><strong>{{ $estadisticas['pendientes'] }}</strong> Por analizar</span></div>
            <div class="project-stat"><i class="bi bi-patch-check"></i><span><strong>{{ $estadisticas['analizados'] }}</strong> Analizados</span></div>
            <div class="project-stat"><i class="bi bi-archive"></i><span><strong>{{ $estadisticas['archivados'] }}</strong> Archivados</span></div>
        </div>

        <div class="catalog-tabs" role="tablist" aria-label="Estado de archivo">
            <a class="catalog-tab {{ $vista === 'activos' ? 'active' : '' }}" href="{{ route('proyectos.index', array_merge(request()->except(['page', 'vista']), ['vista' => 'activos'])) }}">
                <i class="bi bi-folder2-open"></i> Proyectos activos <span>{{ $estadisticas['activos'] }}</span>
            </a>
            <a class="catalog-tab {{ $vista === 'archivados' ? 'active' : '' }}" href="{{ route('proyectos.index', array_merge(request()->except(['page', 'vista']), ['vista' => 'archivados'])) }}">
                <i class="bi bi-archive"></i> Archivo <span>{{ $estadisticas['archivados'] }}</span>
            </a>
        </div>

        <div class="card catalog-card"><div class="card-body">
            <form method="GET" action="{{ route('proyectos.index') }}" class="catalog-filters">
                <input type="hidden" name="vista" value="{{ $vista }}">
                <div class="catalog-search">
                    <label class="form-label" for="buscar">Buscar proyecto o estudiante</label>
                    <div class="input-group"><span class="input-group-text"><i class="bi bi-search"></i></span><input id="buscar" type="search" name="buscar" class="form-control" value="{{ request('buscar') }}" placeholder="Título o nombre del estudiante"></div>
                </div>
                <div><label class="form-label" for="carrera_id">Carrera</label><select id="carrera_id" name="carrera_id" class="form-select"><option value="">Todas</option>@foreach($carreras as $carrera)<option value="{{ $carrera->id }}" @selected(request('carrera_id') == $carrera->id)>{{ $carrera->nombre }}</option>@endforeach</select></div>
                <div><label class="form-label" for="modalidad">Modalidad</label><select id="modalidad" name="modalidad" class="form-select"><option value="">Todas</option>@foreach($modalidades as $valor => $nombre)<option value="{{ $valor }}" @selected(request('modalidad') === $valor)>{{ $nombre }}</option>@endforeach</select></div>
                <div><label class="form-label" for="anio">Gestión</label><select id="anio" name="anio" class="form-select"><option value="">Todas</option>@foreach($anios as $anio)<option value="{{ $anio }}" @selected((string) request('anio') === (string) $anio)>{{ $anio }}</option>@endforeach</select></div>
                <div><label class="form-label" for="estado">Análisis</label><select id="estado" name="estado" class="form-select"><option value="">Todos</option><option value="pendiente_analisis" @selected($estado === 'pendiente_analisis')>Pendiente</option><option value="analizado" @selected($estado === 'analizado')>Analizado</option></select></div>
                <div><label class="form-label" for="orden">Ordenar</label><select id="orden" name="orden" class="form-select"><option value="recientes" @selected($orden === 'recientes')>Más recientes</option><option value="antiguos" @selected($orden === 'antiguos')>Más antiguos</option><option value="titulo_asc" @selected($orden === 'titulo_asc')>Título A–Z</option><option value="titulo_desc" @selected($orden === 'titulo_desc')>Título Z–A</option></select></div>
                <div class="catalog-filter-actions"><button class="btn btn-primary" type="submit"><i class="bi bi-funnel me-1"></i> Aplicar</button><a class="btn btn-outline-secondary" href="{{ route('proyectos.index', ['vista' => $vista]) }}" title="Limpiar filtros"><i class="bi bi-arrow-counterclockwise"></i></a></div>
            </form>

            <div class="catalog-result-summary">
                <span><strong>{{ $proyectos->total() }}</strong> {{ $proyectos->total() === 1 ? 'proyecto encontrado' : 'proyectos encontrados' }}</span>
                <small>{{ $vista === 'archivados' ? 'Los elementos archivados conservan todo su historial.' : 'Sólo los proyectos activos participan en los análisis.' }}</small>
            </div>

            <div class="project-table-wrapper"><table class="table project-table align-middle">
                <thead><tr><th>Proyecto</th><th>Modalidad</th><th>Carrera</th><th>Gestión</th><th>Análisis</th><th>Documento</th><th class="text-end">Acciones</th></tr></thead>
                <tbody>
                    @forelse($proyectos as $proyecto)
                        <tr class="{{ $proyecto->activo ? '' : 'inactive-record' }}">
                            <td><strong class="project-title">{{ $proyecto->titulo }}</strong><small class="project-owner"><i class="bi bi-person"></i> {{ $proyecto->estudiante?->name ?? 'Sin estudiante asignado' }}</small></td>
                            <td><span class="modality-badge">{{ $proyecto->modalidad_nombre }}</span></td>
                            <td>{{ $proyecto->carrera?->codigo ?? $proyecto->carrera?->nombre ?? '—' }}</td>
                            <td>{{ $proyecto->anio }}</td>
                            <td>@if($proyecto->estado === 'analizado')<span class="badge bg-success-subtle text-success-emphasis"><i class="bi bi-check-circle me-1"></i> Analizado</span>@else<span class="badge bg-warning-subtle text-warning-emphasis"><i class="bi bi-clock me-1"></i> Pendiente</span>@endif</td>
                            <td>@if($proyecto->documento)<a href="{{ route('proyectos.documento', $proyecto) }}" target="_blank" rel="noopener" class="document-link"><i class="bi bi-file-earmark-pdf"></i> Ver PDF</a>@else<span class="text-muted small">No disponible</span>@endif</td>
                            <td class="text-end"><div class="dropdown">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Gestionar</button>
                                <ul class="dropdown-menu dropdown-menu-end project-actions">
                                    @if($proyecto->activo && in_array(auth()->user()->rol, ['administrador', 'docente']))
                                        <li><form action="{{ route('proyectos.analizar', $proyecto) }}" method="POST">@csrf<button class="dropdown-item" type="submit"><i class="bi bi-folder-check"></i> Comparar con repositorio</button></form></li>
                                        <li><a class="dropdown-item" href="{{ route('analisis.index', ['proyecto_a' => $proyecto->id]) }}"><i class="bi bi-intersect"></i> Comparar con otro</a></li>
                                        <li><a class="dropdown-item" href="{{ route('proyectos.analisis-externo', $proyecto) }}"><i class="bi bi-globe2"></i> Comparar con fuentes externas</a></li>
                                    @endif
                                    <li><a class="dropdown-item" href="{{ route('proyectos.resultados', $proyecto) }}"><i class="bi bi-bar-chart"></i> Ver resultados</a></li>
                                    @if($proyecto->documento)<li><a class="dropdown-item" target="_blank" rel="noopener" href="{{ route('proyectos.documento', $proyecto) }}"><i class="bi bi-file-earmark-pdf"></i> Abrir documento</a></li>@endif
                                    @if(in_array(auth()->user()->rol, ['administrador', 'docente']))<li><a class="dropdown-item" href="{{ route('proyectos.edit', $proyecto) }}"><i class="bi bi-pencil"></i> Editar datos</a></li>@endif
                                    @if(auth()->user()->rol === 'administrador')
                                        <li><hr class="dropdown-divider"></li>
                                        <li><form action="{{ route('proyectos.archivo', $proyecto) }}" method="POST" onsubmit="return confirm('{{ $proyecto->activo ? '¿Archivar este proyecto? Dejará de participar en los análisis.' : '¿Restaurar este proyecto al catálogo activo?' }}')">@csrf @method('PATCH')<button class="dropdown-item {{ $proyecto->activo ? 'text-warning-emphasis' : 'text-success' }}" type="submit"><i class="bi {{ $proyecto->activo ? 'bi-archive' : 'bi-arrow-counterclockwise' }}"></i> {{ $proyecto->activo ? 'Archivar proyecto' : 'Restaurar proyecto' }}</button></form></li>
                                    @endif
                                </ul>
                            </div></td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="catalog-empty"><i class="bi bi-folder2-open"></i><strong>No hay proyectos en esta vista</strong><span>Prueba cambiando o limpiando los filtros.</span></div></td></tr>
                    @endforelse
                </tbody>
            </table></div>
            {{ $proyectos->links() }}
        </div></div>
    </div>
</x-app-layout>
