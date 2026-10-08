<x-app-layout>
    <x-slot name="header">
        <h2>Carreras</h2>
    </x-slot>

    <div class="container py-4">
        @if(session('success'))
            <x-flash-success :message="session('success')" />
        @endif

        @if(Auth::user()->esAdministrador())
            <a href="{{ route('carreras.create') }}" class="btn btn-primary mb-3">Nueva carrera</a>
        @endif

        <table role="table" class="table mobile-record-table table-striped table-bordered">
            <thead role="rowgroup">
                <tr role="row">
                    <th scope="col" role="columnheader">Nombre</th>
                    <th scope="col" role="columnheader">Código</th>
                    <th scope="col" role="columnheader">Estado</th>
                    <th scope="col" role="columnheader">Acciones</th>
                </tr>
            </thead>
            <tbody role="rowgroup">
                @forelse($carreras as $carrera)
                    <tr role="row" class="{{ $carrera->activo ? '' : 'inactive-record' }}">
                        <td role="cell" data-label="Nombre">{{ $carrera->nombre }}</td>
                        <td role="cell" data-label="Código">{{ $carrera->codigo }}</td>
                        <td role="cell" data-label="Estado"><span class="badge {{ $carrera->activo ? 'bg-success' : 'bg-secondary' }}">{{ $carrera->activo ? 'Activa' : 'Inactiva' }}</span></td>
                        <td role="cell" data-label="Acciones">
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
                    <tr role="row"><td role="cell" colspan="4" class="text-center">No hay carreras registradas.</td></tr>
                @endforelse
            </tbody>
        </table>

        {{ $carreras->links() }}
    </div>
</x-app-layout>
