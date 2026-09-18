<x-app-layout>
    <x-slot name="header"><h2>Editar proyecto</h2></x-slot>

    <div class="container py-4">
        <form action="{{ route('proyectos.update', $proyecto) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Título</label>
                <input type="text" name="titulo" class="form-control @error('titulo') is-invalid @enderror" value="{{ old('titulo', $proyecto->titulo) }}">
                @error('titulo')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Resumen</label>
                <textarea name="resumen" class="form-control @error('resumen') is-invalid @enderror" rows="4">{{ old('resumen', $proyecto->resumen) }}</textarea>
                @error('resumen')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <fieldset class="mb-4">
                <legend class="form-label mb-2">Modalidad de graduación</legend>
                <div class="graduation-modes">
                    @foreach($modalidades as $valor => $nombre)
                        <label class="graduation-mode">
                            <input type="radio" name="modalidad" value="{{ $valor }}" @checked(old('modalidad', $proyecto->modalidad) === $valor)>
                            <span class="graduation-mode-card">
                                <i class="bi {{ match($valor) { 'proyecto_grado' => 'bi-mortarboard', 'sociocomunitario_productivo' => 'bi-people', 'emprendimiento_productivo' => 'bi-lightbulb', default => 'bi-building-check' } }}"></i>
                                <span>{{ $nombre }}</span>
                                <i class="bi bi-check-circle-fill mode-check"></i>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('modalidad')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
            </fieldset>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Carrera</label>
                    <select name="carrera_id" data-project-career class="form-select @error('carrera_id') is-invalid @enderror">
                        @foreach($carreras as $carrera)
                            <option value="{{ $carrera->id }}" @selected(old('carrera_id', $proyecto->carrera_id) == $carrera->id)>{{ $carrera->nombre }}</option>
                        @endforeach
                    </select>
                    @error('carrera_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Estudiante</label>
                    <div class="project-picker @error('estudiante_id') is-invalid @enderror" data-academic-picker data-search-url="{{ route('proyectos.estudiantes.buscar') }}" data-selected-id="{{ old('estudiante_id', $estudianteSeleccionado?->id) }}" data-selected-label="{{ $estudianteSeleccionado?->nombre }}">
                        <input type="hidden" name="estudiante_id" value="{{ old('estudiante_id', $estudianteSeleccionado?->id) }}" data-academic-value>
                        <div class="project-search-field"><i class="bi bi-search"></i><input type="search" autocomplete="off" placeholder="Escribe nombre, código o correo" aria-label="Buscar estudiante" data-academic-search><button type="button" aria-label="Limpiar estudiante" data-academic-clear hidden><i class="bi bi-x-lg"></i></button></div>
                        <div class="project-search-results" data-academic-results role="listbox" hidden></div>
                    </div>
                    @error('estudiante_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text"><a href="{{ route('estudiantes.create') }}">Registrar un estudiante nuevo</a></div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Docente tutor</label>
                    <div class="project-picker @error('tutor_id') is-invalid @enderror" data-academic-picker data-optional="true" data-search-url="{{ route('proyectos.docentes.buscar') }}" data-selected-id="{{ old('tutor_id', $tutorSeleccionado?->id) }}" data-selected-label="{{ $tutorSeleccionado?->nombre }}">
                        <input type="hidden" name="tutor_id" value="{{ old('tutor_id', $tutorSeleccionado?->id) }}" data-academic-value>
                        <div class="project-search-field"><i class="bi bi-search"></i><input type="search" autocomplete="off" placeholder="Escribe nombre, código o especialidad" aria-label="Buscar docente tutor" data-academic-search><button type="button" aria-label="Limpiar docente tutor" data-academic-clear hidden><i class="bi bi-x-lg"></i></button></div>
                        <div class="project-search-results" data-academic-results role="listbox" hidden></div>
                    </div>
                    @error('tutor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text"><a href="{{ route('docentes.create') }}">Registrar un docente tutor nuevo</a></div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Año</label>
                <input type="number" name="anio" class="form-control @error('anio') is-invalid @enderror" value="{{ old('anio', $proyecto->anio) }}">
                @error('anio')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="documento" class="form-label">Reemplazar documento PDF <span class="text-muted fw-normal">(opcional)</span></label>
                <input type="file" name="documento" id="documento" accept="application/pdf" class="form-control @error('documento') is-invalid @enderror">
                @error('documento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                @if($proyecto->documento?->archivo_disponible)
                    <div class="form-text"><i class="bi bi-check-circle text-success me-1"></i>El PDF actual está disponible. Déjalo vacío para conservarlo.</div>
                @elseif($proyecto->documento)
                    <div class="form-text text-warning"><i class="bi bi-exclamation-triangle me-1"></i>El PDF actual no se encuentra. Selecciona el archivo original para reponerlo.</div>
                @endif
            </div>

            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="{{ route('proyectos.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</x-app-layout>
