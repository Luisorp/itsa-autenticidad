<x-app-layout>
    <x-slot name="header"><div class="page-title"><h2>Estudiantes</h2><span>Registro académico independiente de las cuentas de acceso</span></div></x-slot>

    <div class="container py-4">
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

        <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-3">
            <div><h3 class="user-section-title">Padrón de estudiantes</h3><p class="user-section-copy">Estos registros no necesitan contraseña ni una cuenta para iniciar sesión.</p></div>
            <a href="{{ route('estudiantes.create') }}" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i> Nuevo estudiante</a>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h4 class="h6 mb-2"><i class="bi bi-file-earmark-spreadsheet me-1"></i> Carga masiva</h4>
                <p class="text-muted small">Importa un CSV con las columnas: codigo, nombre, email, carrera y gestion. La carrera debe usar su código registrado.</p>
                <form action="{{ route('estudiantes.importar') }}" method="POST" enctype="multipart/form-data" class="d-flex flex-wrap gap-2 align-items-center">
                    @csrf
                    <input type="file" name="archivo" accept=".csv,text/csv" class="form-control" style="max-width: 420px" required>
                    <button class="btn btn-success" type="submit"><i class="bi bi-upload me-1"></i> Importar CSV</button>
                    <a href="{{ route('estudiantes.plantilla') }}" class="btn btn-outline-secondary"><i class="bi bi-download me-1"></i> Descargar plantilla</a>
                </form>
            </div>
        </div>

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-6"><input type="search" name="buscar" value="{{ request('buscar') }}" class="form-control" placeholder="Buscar por nombre, código o correo"></div>
            <div class="col-md-4"><select name="carrera_id" class="form-select"><option value="">Todas las carreras</option>@foreach($carreras as $carrera)<option value="{{ $carrera->id }}" @selected(request('carrera_id') == $carrera->id)>{{ $carrera->nombre }}</option>@endforeach</select></div>
            <div class="col-md-2 d-flex gap-2"><button class="btn btn-primary" type="submit">Filtrar</button><a href="{{ route('estudiantes.index') }}" class="btn btn-outline-secondary">Limpiar</a></div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead><tr><th>Código</th><th>Nombre</th><th>Carrera</th><th>Gestión</th><th>Cuenta</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody>
                    @forelse($estudiantes as $estudiante)
                        <tr class="{{ $estudiante->activo ? '' : 'inactive-record' }}">
                            <td>{{ $estudiante->codigo ?: '—' }}</td><td><strong>{{ $estudiante->nombre }}</strong><small class="d-block text-muted">{{ $estudiante->email ?: 'Sin correo' }}</small></td>
                            <td>{{ $estudiante->carrera?->nombre ?? '—' }}</td><td>{{ $estudiante->gestion_ingreso ?? '—' }}</td>
                            <td>{{ $estudiante->user_id ? 'Vinculada' : 'No requerida' }}</td>
                            <td><span class="badge {{ $estudiante->activo ? 'bg-success' : 'bg-secondary' }}">{{ $estudiante->activo ? 'Activo' : 'Inactivo' }}</span></td>
                            <td><a href="{{ route('estudiantes.edit', $estudiante) }}" class="btn btn-sm btn-warning">Editar</a> <form action="{{ route('estudiantes.toggle-activo', $estudiante) }}" method="POST" class="d-inline">@csrf @method('PATCH')<button class="btn btn-sm {{ $estudiante->activo ? 'btn-secondary' : 'btn-success' }}">{{ $estudiante->activo ? 'Desactivar' : 'Activar' }}</button></form></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4">No hay estudiantes registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $estudiantes->links() }}
    </div>
</x-app-layout>
