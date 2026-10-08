<x-app-layout>
    <x-slot name="header"><div class="page-title"><h2>Comparar proyectos</h2><span>Comparación interna de documentos</span></div></x-slot>

    <div class="container py-4">
        <div class="analysis-intro mb-4">
            <div class="analysis-intro-icon"><i class="bi bi-intersect"></i></div>
            <div>
                <h3>Comparación guiada</h3>
                <p>Selecciona dos proyectos de la misma modalidad. Se comparan resumen, introducción, desarrollo y conclusiones. Se excluyen portada, tutor, índices, marco teórico o capítulo II, bibliografía y anexos.</p>
            </div>
        </div>

        <form action="{{ route('analisis.comparar') }}" method="POST" data-comparison-form data-search-url="{{ route('analisis.proyectos.buscar') }}">
            @csrf

            <div class="row align-items-start">
                <div class="col-md-5 mb-3">
                    <label class="form-label">Proyecto A</label>
                    <div class="project-picker @error('proyecto_a') is-invalid @enderror" data-project-picker="a"
                         data-selected-id="{{ $proyectoA?->id }}" data-selected-title="{{ $proyectoA?->titulo }}"
                         data-selected-modalidad="{{ $proyectoA?->modalidad }}" data-selected-modalidad-name="{{ $proyectoA?->modalidad_nombre }}"
                         data-selected-student="{{ $proyectoA?->estudiante?->nombre }}" data-selected-career="{{ $proyectoA?->carrera?->codigo ?? $proyectoA?->carrera?->nombre }}" data-selected-year="{{ $proyectoA?->anio }}">
                        <input type="hidden" name="proyecto_a" value="{{ $proyectoA?->id }}" data-project-value>
                        <div class="project-search-field"><i class="bi bi-search"></i><input type="search" autocomplete="off" placeholder="Escribe título, estudiante o carrera…" aria-label="Buscar Proyecto A" data-project-search><button type="button" aria-label="Limpiar Proyecto A" data-project-clear><i class="bi bi-x-lg"></i></button></div>
                        <div class="project-search-results" data-project-results role="listbox" hidden></div>
                    </div>
                    @error('proyecto_a')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-2 mb-3 d-flex align-items-center justify-content-center">
                    <span class="comparison-vs">VS</span>
                </div>

                <div class="col-md-5 mb-3">
                    <label class="form-label">Proyecto B</label>
                    <div class="project-picker @error('proyecto_b') is-invalid @enderror" data-project-picker="b"
                         data-selected-id="{{ $proyectoB?->id }}" data-selected-title="{{ $proyectoB?->titulo }}"
                         data-selected-modalidad="{{ $proyectoB?->modalidad }}" data-selected-modalidad-name="{{ $proyectoB?->modalidad_nombre }}"
                         data-selected-student="{{ $proyectoB?->estudiante?->nombre }}" data-selected-career="{{ $proyectoB?->carrera?->codigo ?? $proyectoB?->carrera?->nombre }}" data-selected-year="{{ $proyectoB?->anio }}">
                        <input type="hidden" name="proyecto_b" value="{{ $proyectoB?->id }}" data-project-value>
                        <div class="project-search-field"><i class="bi bi-search"></i><input type="search" autocomplete="off" placeholder="Primero selecciona el Proyecto A" aria-label="Buscar Proyecto B" data-project-search><button type="button" aria-label="Limpiar Proyecto B" data-project-clear><i class="bi bi-x-lg"></i></button></div>
                        <div class="project-search-results" data-project-results role="listbox" hidden></div>
                    </div>
                    @error('proyecto_b')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>

            <p class="comparison-hint" data-comparison-hint><i class="bi bi-shield-check"></i> Busca entre proyectos activos con texto disponible. Proyecto B se limita automáticamente a la modalidad de Proyecto A.</p>

            <button type="submit" class="btn btn-primary"><i class="bi bi-intersect me-1"></i> Comparar documentos</button>
        </form>

        @isset($porcentaje)
            <div class="alert alert-info mt-4">
                <strong>Contenido analizado</strong><br>
                Proyecto A: {{ implode(', ', $alcanceA['secciones']) }}.<br>
                Proyecto B: {{ implode(', ', $alcanceB['secciones']) }}.
            </div>
            @php
                $clase = $porcentaje >= 70 ? 'danger' : ($porcentaje >= 40 ? 'warning' : 'success');
            @endphp
            <div class="alert alert-{{ $clase }} mt-4 text-center">
                <h3 class="mb-0">{{ $porcentaje }}% de similitud</h3>
                <p class="mb-0">entre "{{ $proyectoA->titulo }}" y "{{ $proyectoB->titulo }}"</p>
            </div>

            <div class="score-explanation">
                <div class="score-explanation-icon"><i class="bi bi-calculator"></i></div>
                <div class="score-explanation-content">
                    <h4>¿De dónde sale el {{ $porcentaje }}%?</h4>
                    <p>
                        Se comparan secuencias de cinco palabras consecutivas, manteniendo su orden, cifras y negaciones.
                        Se encontraron <strong>{{ $explicacion['secuencias_compartidas'] }} secuencias compartidas</strong>
                        de {{ $explicacion['secuencias_proyecto_a'] }} en el Proyecto A y {{ $explicacion['secuencias_proyecto_b'] }} en el Proyecto B.
                        El porcentaje es dos veces las secuencias compartidas dividido entre el total de secuencias de ambos proyectos.
                        Las secuencias coincidentes representan {{ $explicacion['cobertura_proyecto_a'] }}% del texto comparable de A y {{ $explicacion['cobertura_proyecto_b'] }}% de B.
                        Se excluyen rótulos de tablas y figuras, fuentes y campos de plantilla.
                    </p>
                    @if($explicacion['principales_terminos'] !== [])
                        <div class="contributing-terms">
                            <span>Vocabulario compartido (dato secundario; no determina el porcentaje):</span>
                            @foreach($explicacion['principales_terminos'] as $termino)
                                <mark>{{ $termino }}</mark>
                            @endforeach
                        </div>
                    @endif
                    @if($coincidencias === [])
                        <p class="score-clarification">
                            <i class="bi bi-info-circle"></i>
                            Puede haber secuencias breves compartidas sin un fragmento suficientemente extenso: se muestran bloques con al menos tres secuencias coincidentes y {{ $explicacion['umbral_fragmentos'] }}% de coincidencia textual.
                        </p>
                    @endif
                </div>
            </div>

            <section class="match-report mt-4">
                <div class="match-report-heading">
                    <div>
                        <span class="section-kicker">Evidencia textual</span>
                        <h3>Fragmentos con posibles coincidencias</h3>
                    </div>
                    <span class="match-count">{{ count($coincidencias) }} encontrados</span>
                </div>

                <div class="similarity-notice">
                    <i class="bi bi-shield-exclamation"></i>
                    <span>Estos resultados son indicadores de apoyo. Una coincidencia puede corresponder a citas, terminología técnica o contenido común y no constituye por sí sola una prueba de plagio.</span>
                </div>

                @forelse($coincidencias as $indice => $coincidencia)
                    @php
                        $nivel = $coincidencia['porcentaje'] >= 85 ? 'high' : ($coincidencia['porcentaje'] >= 65 ? 'medium' : 'review');
                    @endphp
                    <article class="match-card match-{{ $nivel }}">
                        <div class="match-card-top">
                            <span class="match-number">Coincidencia {{ $indice + 1 }}</span>
                            <span class="match-score">{{ $coincidencia['porcentaje'] }}% de coincidencia textual</span>
                        </div>
                        <div class="match-columns">
                            <div class="match-fragment">
                                <small>Proyecto A · {{ $proyectoA->titulo }}</small>
                                <p>{{ $coincidencia['fragmento_a'] }}</p>
                            </div>
                            <div class="match-link" aria-hidden="true"><i class="bi bi-arrow-left-right"></i></div>
                            <div class="match-fragment">
                                <small>Proyecto B · {{ $proyectoB->titulo }}</small>
                                <p>{{ $coincidencia['fragmento_b'] }}</p>
                            </div>
                        </div>
                        <div class="shared-words">
                            <span>Conceptos compartidos:</span>
                            @foreach($coincidencia['palabras_comunes'] as $palabra)
                                <mark>{{ $palabra }}</mark>
                            @endforeach
                        </div>
                    </article>
                @empty
                    <div class="empty-matches">
                        <i class="bi bi-file-earmark-check"></i>
                        <h4>No se detectaron fragmentos relevantes</h4>
                        <p>No hay bloques de texto suficientemente parecidos para mostrarlos como evidencia.</p>
                    </div>
                @endforelse
            </section>
        @endisset
    </div>
</x-app-layout>
