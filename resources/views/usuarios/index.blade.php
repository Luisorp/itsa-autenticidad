<x-app-layout>
    <x-slot name="header"><h2>Usuarios</h2></x-slot>

    <div class="container py-4">
        @if(session('success'))
            <x-flash-success :message="session('success')" />
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
                        <i class="bi {{ match($valor) { 'administrador' => 'bi-shield-lock', 'gestor' => 'bi-person-workspace', default => 'bi-person' } }}"></i>
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

        <table role="table" class="table mobile-record-table table-striped table-bordered align-middle">
            <thead role="rowgroup">
                <tr role="row">
                    <th scope="col" role="columnheader">Nombre</th>
                    <th scope="col" role="columnheader">Correo</th>
                    <th scope="col" role="columnheader">Rol</th>
                    <th scope="col" role="columnheader">Carrera</th>
                    <th scope="col" role="columnheader">Estado</th>
                    <th scope="col" role="columnheader">Acciones</th>
                </tr>
            </thead>
            <tbody role="rowgroup">
                @forelse($usuarios as $usuario)
                    <tr role="row" class="{{ $usuario->activo ? '' : 'inactive-record' }}">
                        <td role="cell" data-label="Nombre">{{ $usuario->name }}</td>
                        <td role="cell" data-label="Correo">{{ $usuario->email }}</td>
                        <td role="cell" data-label="Rol"><span class="badge bg-secondary">{{ ucfirst($usuario->rol) }}</span></td>
                        <td role="cell" data-label="Carrera">{{ $usuario->carrera->nombre ?? '—' }}</td>
                        <td role="cell" data-label="Estado">
                            <span class="badge {{ $usuario->activo ? 'bg-success' : 'bg-secondary' }}">
                                {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td role="cell" data-label="Acciones">
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
                    <tr role="row"><td role="cell" colspan="6" class="text-center">No hay usuarios registrados.</td></tr>
                @endforelse
            </tbody>
        </table>

        {{ $usuarios->links() }}
    </div>
</x-app-layout>
