<x-app-layout>
    <x-slot name="header"><h2>Respaldos de la base de datos</h2></x-slot>

    <div class="container py-4">
        @if(session('success'))
            <x-flash-success :message="session('success')" />
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form action="{{ route('respaldos.generar') }}" method="POST" class="mb-4">
            @csrf
            <button type="submit" class="btn btn-primary">Generar nuevo respaldo</button>
        </form>

        <table role="table" class="table mobile-record-table table-bordered">
            <thead role="rowgroup">
                <tr role="row">
                    <th scope="col" role="columnheader">Archivo</th>
                    <th scope="col" role="columnheader">Tamaño (KB)</th>
                    <th scope="col" role="columnheader">Fecha</th>
                    <th scope="col" role="columnheader">Acción</th>
                </tr>
            </thead>
            <tbody role="rowgroup">
                @forelse($archivos as $archivo)
                    <tr role="row">
                        <td role="cell" data-label="Archivo">{{ $archivo['nombre'] }}</td>
                        <td role="cell" data-label="Tamaño (KB)">{{ $archivo['tamano'] }}</td>
                        <td role="cell" data-label="Fecha">{{ $archivo['fecha'] }}</td>
                        <td role="cell" data-label="Acción">
                            <a href="{{ route('respaldos.descargar', $archivo['nombre']) }}" class="btn btn-sm btn-outline-primary">Descargar</a>
                        </td>
                    </tr>
                @empty
                    <tr role="row"><td role="cell" colspan="4" class="text-center">Todavía no se ha generado ningún respaldo.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>
