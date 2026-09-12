<x-app-layout>
    <x-slot name="header"><h2>Usuarios</h2></x-slot>

    <div class="container py-4">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-3">
            <div>
                <h3 class="user-section-title">Gestión de usuarios</h3>
                <p class="user-section-copy">Administra cada tipo de cuenta por separado.</p>
            </div>
            <a href="{{ route('usuarios.create') }}" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i> Nuevo usuario</a>
        </div>

        <nav class="user-role-tabs" aria-label="Clasificación de usuarios">
            @foreach($roles as $valor => $nombre)
                <a href="{{ route('usuarios.index', ['rol' => $valor]) }}" class="user-role-tab {{ $rolActual === $valor ? 'active' : '' }}" @if($rolActual === $valor) aria-current="page" @endif>
                    <span class="user-role-icon">
                        <i class="bi {{ match($valor) { 'administrador' => 'bi-shield-lock', 'docente' => 'bi-person-workspace', default => 'bi-mortarboard' } }}"></i>
                    </span>
                    <span><strong>{{ $nombre }}</strong><small>{{ $conteos[$valor] ?? 0 }} registrados</small></span>
                    <span class="user-role-count">{{ $conteos[$valor] ?? 0 }}</span>
                </a>
            @endforeach
        </nav>

        <div class="user-list-heading">
            <span>Mostrando</span>
            <strong>{{ $roles[$rolActual] }}</strong>
        </div>

        <table class="table table-striped table-bordered align-middle">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Carrera</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usuarios as $usuario)
                    <tr class="{{ $usuario->activo ? '' : 'inactive-record' }}">
                        <td>{{ $usuario->name }}</td>
                        <td>{{ $usuario->email }}</td>
                        <td><span class="badge bg-secondary">{{ ucfirst($usuario->rol) }}</span></td>
                        <td>{{ $usuario->carrera->nombre ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $usuario->activo ? 'bg-success' : 'bg-secondary' }}">
                                {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('usuarios.edit', $usuario) }}" class="btn btn-sm btn-warning">Editar</a>
                            <form action="{{ route('usuarios.toggle-activo', $usuario) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $usuario->activo ? 'btn-secondary' : 'btn-success' }}">
                                    {{ $usuario->activo ? 'Desactivar' : 'Activar' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">No hay usuarios registrados.</td></tr>
                @endforelse
            </tbody>
        </table>

        {{ $usuarios->links() }}
    </div>
</x-app-layout>
