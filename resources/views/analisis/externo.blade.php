<x-app-layout>
    <x-slot name="header"><div class="page-title"><h2>Comparar con fuentes externas</h2><span>Publicaciones académicas de Crossref y OpenAlex</span></div></x-slot>

    <div class="container py-4 external-analysis">
        <section class="analysis-intro mb-4">
            <div class="analysis-intro-icon"><i class="bi bi-globe2"></i></div>
            <div>
                <span class="section-kicker">Proyecto seleccionado</span>
                <h3>{{ $proyecto->titulo }}</h3>
                <p>{{ $proyecto->modalidad_nombre }} · {{ $proyecto->estudiante?->name }} · {{ $proyecto->carrera?->nombre }}</p>
            </div>
        </section>

        <div class="external-scope mb-4">
            <i class="bi bi-info-circle"></i>
            <div><strong>¿Qué comprobará este análisis?</strong><p>Buscará publicaciones relacionadas en Crossref y OpenAlex, y comparará el proyecto con los títulos y resúmenes disponibles. No representa una búsqueda de todo Internet ni una prueba automática de plagio.</p></div>
        </div>

        <form action="{{ route('proyectos.analisis-externo.analizar', $proyecto) }}" method="POST" class="mb-4">
            @csrf
            <button type="submit" class="btn btn-primary"><i class="bi bi-radar me-1"></i> Buscar y comparar fuentes</button>
            <a href="{{ route('proyectos.index') }}" class="btn btn-outline-secondary">Volver a proyectos</a>
        </form>

        @isset($resultados)
            @if($fuentesNoDisponibles !== [])
                <div class="alert alert-warning">No se pudo consultar {{ implode(' y ', $fuentesNoDisponibles) }}. Se muestran los resultados de las fuentes disponibles.</div>
            @endif

            <div class="external-results-heading">
                <div><span class="section-kicker">Resultados orientativos</span><h3>Coincidencias académicas externas</h3></div>
                <span>{{ count($resultados) }} publicaciones revisadas</span>
            </div>

            @forelse($resultados as $resultado)
                @php $nivel = $resultado['porcentaje'] >= 70 ? 'high' : ($resultado['porcentaje'] >= 40 ? 'medium' : 'low'); @endphp
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
                                    <div><p><strong>Proyecto:</strong> {{ $coincidencia['fragmento_a'] }}</p><p><strong>Fuente:</strong> {{ $coincidencia['fragmento_b'] }}</p><span>{{ $coincidencia['porcentaje'] }}% en el fragmento</span></div>
                                @endforeach
                            </details>
                        @endif
                        @if($resultado['url'])<a href="{{ $resultado['url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary mt-3">Consultar publicación <i class="bi bi-box-arrow-up-right ms-1"></i></a>@endif
                    </div>
                </article>
            @empty
                <div class="empty-matches"><i class="bi bi-search"></i><h4>No se encontraron publicaciones comparables</h4><p>Las fuentes consultadas no devolvieron títulos o resúmenes relacionados con este proyecto.</p></div>
            @endforelse
        @endisset
    </div>
</x-app-layout>
