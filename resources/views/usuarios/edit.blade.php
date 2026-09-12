<x-app-layout>
    <x-slot name="header"><h2>Editar usuario</h2></x-slot>

    <div class="container py-4">
        <form action="{{ route('usuarios.update', $usuario) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Nombre</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $usuario->name) }}">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Correo</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $usuario->email) }}">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Rol</label>
                    <select name="rol" class="form-select @error('rol') is-invalid @enderror">
                        <option value="estudiante" @selected(old('rol', $usuario->rol) == 'estudiante')>Estudiante</option>
                        <option value="docente" @selected(old('rol', $usuario->rol) == 'docente')>Docente</option>
                        <option value="administrador" @selected(old('rol', $usuario->rol) == 'administrador')>Administrador</option>
                    </select>
                    @error('rol')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Carrera</label>
                    <select name="carrera_id" class="form-select @error('carrera_id') is-invalid @enderror">
                        <option value="">Sin asignar</option>
                        @foreach($carreras as $carrera)
                            <option value="{{ $carrera->id }}" @selected(old('carrera_id', $usuario->carrera_id) == $carrera->id)>{{ $carrera->nombre }}</option>
                        @endforeach
                    </select>
                    @error('carrera_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</x-app-layout>