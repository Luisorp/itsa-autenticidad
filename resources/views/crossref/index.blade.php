<x-app-layout>
    <x-slot name="header"><div class="page-title"><h2>Búsqueda académica</h2><span>Consulta bibliográfica externa</span></div></x-slot>

    <div class="container py-4">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="mb-2">Buscar publicaciones académicas</h5>
                <p class="text-muted mb-4">Busca publicaciones por título o DOI y elige la fuente académica que mejor se adapte a tu consulta.</p>

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form action="{{ route('crossref.index') }}" method="GET">
                    <fieldset class="reference-sources mb-4">
                        <legend class="form-label">Fuente de búsqueda</legend>
                        <label class="reference-source">
                            <input type="radio" name="fuente" value="crossref" @checked(old('fuente', $datos['fuente'] ?? 'crossref') === 'crossref')>
                            <span class="reference-source-icon"><i class="bi bi-journal-text"></i></span>
                            <span><strong>Crossref</strong><small>DOI y metadatos editoriales oficiales</small></span>
                            <i class="bi bi-check-circle-fill reference-source-check"></i>
                        </label>
                        <label class="reference-source">
                            <input type="radio" name="fuente" value="openalex" @checked(old('fuente', $datos['fuente'] ?? '') === 'openalex')>
                            <span class="reference-source-icon"><i class="bi bi-diagram-3"></i></span>
                            <span><strong>OpenAlex</strong><small>Autores, citas y acceso abierto</small></span>
                            <i class="bi bi-check-circle-fill reference-source-check"></i>
                        </label>
                    </fieldset>
                    @error('fuente')<div class="text-danger small mb-3">{{ $message }}</div>@enderror
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="tipo" class="form-label">Buscar por</label>
                            <select id="tipo" name="tipo" class="form-select @error('tipo') is-invalid @enderror">
                                <option value="titulo" @selected(old('tipo', $datos['tipo'] ?? 'titulo') === 'titulo')>Título o referencia</option>
                                <option value="doi" @selected(old('tipo', $datos['tipo'] ?? '') === 'doi')>DOI exacto</option>
                            </select>
                            @error('tipo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-7">
                            <label for="consulta" class="form-label">Consulta</label>
                            <input id="consulta" name="consulta" type="text" value="{{ old('consulta', $datos['consulta'] ?? '') }}"
                                   class="form-control @error('consulta') is-invalid @enderror"
                                   placeholder="Ej.: inteligencia artificial educativa o 10.1000/xyz123" required>
                            @error('consulta')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-2 d-grid">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i> Buscar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        @if(is_array($resultados))
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Resultados en {{ ($datos['fuente'] ?? 'crossref') === 'openalex' ? 'OpenAlex' : 'Crossref' }}</h5>
                <span class="badge text-bg-secondary">{{ count($resultados) }} encontrado(s)</span>
            </div>

            @forelse($resultados as $resultado)
                <article class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                            <h5 class="mb-0">{{ $resultado['titulo'] }}</h5>
                            <div class="d-flex gap-2"><span class="badge reference-provider-badge">{{ $resultado['fuente'] ?? 'Crossref' }}</span>@if($resultado['tipo'])<span class="badge text-bg-light">{{ $resultado['tipo'] }}</span>@endif</div>
                        </div>
                        <p class="text-muted mb-2">
                            {{ $resultado['autores'] ? implode(', ', $resultado['autores']) : 'Autores no registrados' }}
                        </p>
                        <div class="small mb-3">
                            @if($resultado['publicacion'])<span class="me-3"><strong>Publicación:</strong> {{ $resultado['publicacion'] }}</span>@endif
                            @if($resultado['fecha'])<span><strong>Fecha:</strong> {{ $resultado['fecha'] }}</span>@endif
                            @if(($resultado['citas'] ?? null) !== null)<span class="ms-3"><strong>Citado por:</strong> {{ $resultado['citas'] }}</span>@endif
                            @if(($resultado['acceso_abierto'] ?? null) === true)<span class="ms-3 text-success"><i class="bi bi-unlock"></i> Acceso abierto</span>@endif
                        </div>
                        @if($resultado['resumen'])
                            <p class="small">{{ $resultado['resumen'] }}</p>
                        @endif
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            @if($resultado['doi'])<code>{{ $resultado['doi'] }}</code>@endif
                            @if($resultado['url'])
                                <a href="{{ $resultado['url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
                                    Abrir publicación <i class="bi bi-box-arrow-up-right ms-1"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="alert alert-info">No se encontraron publicaciones para esa consulta.</div>
            @endforelse
        @endif
    </div>
</x-app-layout>
