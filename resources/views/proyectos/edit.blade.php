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
                    <select name="carrera_id" class="form-select @error('carrera_id') is-invalid @enderror">
                        @foreach($carreras as $carrera)
                            <option value="{{ $carrera->id }}" @selected(old('carrera_id', $proyecto->carrera_id) == $carrera->id)>{{ $carrera->nombre }}</option>
                        @endforeach
                    </select>
                    @error('carrera_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Estudiante</label>
                    <select name="estudiante_id" class="form-select @error('estudiante_id') is-invalid @enderror">
                        @foreach($estudiantes as $estudiante)
                            <option value="{{ $estudiante->id }}" @selected(old('estudiante_id', $proyecto->estudiante_id) == $estudiante->id)>{{ $estudiante->name }}</option>
                        @endforeach
                    </select>
                    @error('estudiante_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Tutor</label>
                    <select name="tutor_id" class="form-select @error('tutor_id') is-invalid @enderror">
                        <option value="">Sin asignar</option>
                        @foreach($tutores as $tutor)
                            <option value="{{ $tutor->id }}" @selected(old('tutor_id', $proyecto->tutor_id) == $tutor->id)>{{ $tutor->name }}</option>
                        @endforeach
                    </select>
                    @error('tutor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
