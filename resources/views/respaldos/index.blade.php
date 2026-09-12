<x-app-layout>
    <x-slot name="header"><h2>Respaldos de la base de datos</h2></x-slot>

    <div class="container py-4">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form action="{{ route('respaldos.generar') }}" method="POST" class="mb-4">
            @csrf
            <button type="submit" class="btn btn-primary">Generar nuevo respaldo</button>
        </form>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Archivo</th>
                    <th>Tamaño (KB)</th>
                    <th>Fecha</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                @forelse($archivos as $archivo)
                    <tr>
                        <td>{{ $archivo['nombre'] }}</td>
                        <td>{{ $archivo['tamano'] }}</td>
                        <td>{{ $archivo['fecha'] }}</td>
                        <td>
                            <a href="{{ route('respaldos.descargar', $archivo['nombre']) }}" class="btn btn-sm btn-outline-primary">Descargar</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center">Todavía no se ha generado ningún respaldo.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>