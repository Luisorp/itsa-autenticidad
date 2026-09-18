<div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Código institucional</label><input name="codigo" class="form-control @error('codigo') is-invalid @enderror" value="{{ old('codigo', $docente->codigo ?? '') }}">@error('codigo')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-8 mb-3"><label class="form-label">Nombre completo</label><input name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $docente->nombre ?? '') }}" required>@error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-6 mb-3"><label class="form-label">Correo <span class="text-muted">(opcional)</span></label><input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $docente->email ?? '') }}">@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-6 mb-3"><label class="form-label">Especialidad <span class="text-muted">(opcional)</span></label><input name="especialidad" class="form-control @error('especialidad') is-invalid @enderror" value="{{ old('especialidad', $docente->especialidad ?? '') }}">@error('especialidad')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-6 mb-3"><label class="form-label">Cuenta de gestor <span class="text-muted">(opcional)</span></label><select name="user_id" class="form-select @error('user_id') is-invalid @enderror"><option value="">No necesita iniciar sesión</option>@foreach($cuentas as $cuenta)<option value="{{ $cuenta->id }}" @selected(old('user_id', $docente->user_id ?? '') == $cuenta->id)>{{ $cuenta->name }} · {{ $cuenta->email }}</option>@endforeach</select>@error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror<div class="form-text">Úsala si este docente también gestionará proyectos dentro del sistema.</div></div>
    <div class="col-12 mb-3">
        <fieldset>
            <legend class="form-label mb-2">Carreras asignadas</legend>
            @php($carrerasSeleccionadas = collect(old('carrera_ids', isset($docente) ? $docente->carreras->pluck('id')->all() : []))->map(fn ($id) => (string) $id))
            <div class="row g-2">
                @foreach($carreras as $carrera)
                    <div class="col-md-6 col-xl-4">
                        <label class="border rounded-3 p-3 d-flex gap-2 align-items-center h-100 bg-light">
                            <input type="checkbox" name="carrera_ids[]" value="{{ $carrera->id }}" class="form-check-input mt-0" @checked($carrerasSeleccionadas->contains((string) $carrera->id))>
                            <span><strong class="d-block">{{ $carrera->nombre }}</strong><small class="text-muted">{{ $carrera->codigo }}</small></span>
                        </label>
                    </div>
                @endforeach
            </div>
            @error('carrera_ids')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
            @error('carrera_ids.*')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
            <div class="form-text">Puedes marcar varias carreras para el mismo docente tutor.</div>
        </fieldset>
    </div>
</div>
<p class="form-text">Registrar un docente aquí no crea una cuenta de acceso al sistema.</p>
<button class="btn btn-primary" type="submit">{{ $textoBoton }}</button><a href="{{ route('docentes.index') }}" class="btn btn-secondary">Cancelar</a>
