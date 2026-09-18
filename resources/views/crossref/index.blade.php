<x-app-layout>
    <x-slot name="header"><div class="page-title"><h2>Búsqueda académica</h2><span>Comparación con publicaciones de Crossref y OpenAlex</span></div></x-slot>

    <div class="container py-4 external-analysis">
        <section class="analysis-intro mb-4">
            <div class="analysis-intro-icon"><i class="bi bi-globe2"></i></div>
            <div>
                <span class="section-kicker">Comparación externa</span>
                <h3>Selecciona un proyecto para buscar coincidencias</h3>
                <p>El sistema consultará las dos fuentes y comparará el texto del proyecto con los títulos y resúmenes disponibles.</p>
            </div>
        </section>

        <div class="external-scope mb-4">
            <i class="bi bi-info-circle"></i>
            <div><strong>Alcance de la búsqueda</strong><p>Crossref aporta DOI y metadatos editoriales; OpenAlex aporta publicaciones, citas y resúmenes cuando están disponibles. Esto no equivale a revisar todo Internet ni constituye por sí solo una prueba de plagio.</p></div>
        </div>

        <form action="{{ route('crossref.comparar') }}" method="POST" class="card border-0 shadow-sm mb-4" data-external-search-form data-project-search-url="{{ route('analisis.proyectos.buscar') }}">
            @csrf
            <div class="card-body p-4">
                <div class="reference-sources mb-4">
                    <div class="reference-source">
                        <span class="reference-source-icon"><i class="bi bi-journal-text"></i></span>
                        <span><strong>Crossref</strong><small>DOI y metadatos editoriales oficiales</small></span>
                        <i class="bi bi-check-circle-fill text-success ms-auto"></i>
                    </div>
                    <div class="reference-source">
                        <span class="reference-source-icon"><i class="bi bi-diagram-3"></i></span>
                        <span><strong>OpenAlex</strong><small>Autores, citas, acceso abierto y resúmenes</small></span>
                        <i class="bi bi-check-circle-fill text-success ms-auto"></i>
                    </div>
                </div>

                <label class="form-label">Proyecto que se comparará</label>
                <div class="project-picker @error('proyecto_id') is-invalid @enderror" data-external-project-picker
                     data-selected-id="{{ old('proyecto_id', $proyecto?->id) }}"
                     data-selected-title="{{ $proyecto?->titulo }}"
                     data-selected-student="{{ $proyecto?->estudiante?->nombre }}"
                     data-selected-career="{{ $proyecto?->carrera?->codigo ?? $proyecto?->carrera?->nombre }}">
                    <input type="hidden" name="proyecto_id" value="{{ old('proyecto_id', $proyecto?->id) }}" data-external-project-value>
                    <div class="project-search-field">
                        <i class="bi bi-search"></i>
                        <input type="search" autocomplete="off" placeholder="Escribe el título, estudiante o carrera" aria-label="Buscar proyecto" data-external-project-search>
                        <button type="button" aria-label="Limpiar proyecto" data-external-project-clear hidden><i class="bi bi-x-lg"></i></button>
                    </div>
                    <div class="project-search-results" data-external-project-results role="listbox" hidden></div>
                </div>
                @error('proyecto_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

                <div class="d-flex flex-wrap gap-2 mt-3">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-radar me-1"></i> Buscar y comparar en ambas APIs</button>
                    <a href="{{ route('proyectos.index') }}" class="btn btn-outline-secondary">Volver a proyectos</a>
                </div>
            </div>
        </form>

        @if($proyecto)
            <section class="card border-0 shadow-sm mb-4"><div class="card-body d-flex align-items-center gap-3">
                <span class="metric-icon"><i class="bi bi-file-earmark-text"></i></span>
                <div><span class="section-kicker">Proyecto seleccionado</span><h4 class="h6 mb-1">{{ $proyecto->titulo }}</h4><p class="small text-muted mb-0">{{ $proyecto->modalidad_nombre }} · {{ $proyecto->estudiante?->nombre }} · {{ $proyecto->carrera?->nombre }}</p></div>
            </div></section>
        @endif

        @isset($resultados)
            @if($fuentesNoDisponibles !== [])
                <div class="alert alert-warning">No se pudo consultar {{ implode(' y ', $fuentesNoDisponibles) }}. Se muestran los resultados de las fuentes disponibles.</div>
            @endif

            <div class="external-results-heading">
                <div><span class="section-kicker">Resultados orientativos</span><h3>Coincidencias académicas externas</h3></div>
                <span>{{ count($resultados) }} publicaciones revisadas</span>
            </div>

            @forelse($resultados as $resultado)
                @php($nivel = $resultado['porcentaje'] >= 70 ? 'high' : ($resultado['porcentaje'] >= 40 ? 'medium' : 'low'))
                <article class="external-result external-result-{{ $nivel }}">
                    <div class="external-score"><strong>{{ number_format($resultado['porcentaje'], 2) }}%</strong><span>similitud</span></div>
                    <div class="external-result-body">
                        <div class="external-result-top"><span class="reference-provider-badge">{{ $resultado['fuente'] }}</span><small>{{ $resultado['alcance'] }}</small></div>
                        <h4>{{ $resultado['titulo'] }}</h4>
                        <p class="external-authors">{{ $resultado['autores'] ? implode(', ', $resultado['autores']) : 'Autores no registrados' }}</p>
                        <div class="external-metadata">
                            @if($resultado['publicacion'])<span><i class="bi bi-journal"></i> {{ $resultado['publicacion'] }}</span>@endif
                            @if($resultado['fecha'])<span><i class="bi bi-calendar3"></i> {{ $resultado['fecha'] }}</span>@endif
                            @if($resultado['doi'])<span><i class="bi bi-fingerprint"></i> {{ $resultado['doi'] }}</span>@endif
                        </div>
                        @if($resultado['coincidencias'] !== [])
                            <details class="external-matches"><summary>Ver {{ count($resultado['coincidencias']) }} fragmento(s) relacionado(s)</summary>
                                @foreach($resultado['coincidencias'] as $coincidencia)
                                    <div><p><strong>Proyecto:</strong> {{ $coincidencia['fragmento_a'] }}</p><p><strong>Publicación:</strong> {{ $coincidencia['fragmento_b'] }}</p><span>{{ $coincidencia['porcentaje'] }}% en el fragmento</span></div>
                                @endforeach
                            </details>
                        @endif
                        @if($resultado['url'])<a href="{{ $resultado['url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary mt-3">Consultar publicación <i class="bi bi-box-arrow-up-right ms-1"></i></a>@endif
                    </div>
                </article>
            @empty
                <div class="empty-matches"><i class="bi bi-search"></i><h4>No se encontraron publicaciones comparables</h4><p>Crossref y OpenAlex no devolvieron títulos o resúmenes relacionados con este proyecto.</p></div>
            @endforelse
        @endisset
    </div>
</x-app-layout>
