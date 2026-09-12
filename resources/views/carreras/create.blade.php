<x-app-layout>
    <x-slot name="header">
        <h2>Nueva carrera</h2>
    </x-slot>

    <div class="container py-4">
        <form action="{{ route('carreras.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" name="nombre" id="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre') }}">
                @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="codigo" class="form-label">Código</label>
                <input type="text" name="codigo" id="codigo" class="form-control @error('codigo') is-invalid @enderror" value="{{ old('codigo') }}">
                @error('codigo')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="{{ route('carreras.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</x-app-layout>