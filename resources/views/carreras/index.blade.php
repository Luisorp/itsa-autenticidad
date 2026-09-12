<x-app-layout>
    <x-slot name="header">
        <h2>Carreras</h2>
    </x-slot>

    <div class="container py-4">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(Auth::user()->esAdministrador())
            <a href="{{ route('carreras.create') }}" class="btn btn-primary mb-3">Nueva carrera</a>
        @endif

        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Código</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($carreras as $carrera)
                    <tr class="{{ $carrera->activo ? '' : 'inactive-record' }}">
                        <td>{{ $carrera->nombre }}</td>
                        <td>{{ $carrera->codigo }}</td>
                        <td><span class="badge {{ $carrera->activo ? 'bg-success' : 'bg-secondary' }}">{{ $carrera->activo ? 'Activa' : 'Inactiva' }}</span></td>
                        <td>
                            @if(Auth::user()->esAdministrador())
                                <a href="{{ route('carreras.edit', $carrera) }}" class="btn btn-sm btn-warning">Editar</a>
                                <form action="{{ route('carreras.toggle-activo', $carrera) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $carrera->activo ? 'btn-secondary' : 'btn-success' }}">{{ $carrera->activo ? 'Desactivar' : 'Activar' }}</button>
                                </form>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center">No hay carreras registradas.</td></tr>
                @endforelse
            </tbody>
        </table>

        {{ $carreras->links() }}
    </div>
</x-app-layout>
