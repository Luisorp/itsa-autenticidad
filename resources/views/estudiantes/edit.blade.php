<x-app-layout>
    <x-slot name="header"><h2>Editar estudiante</h2></x-slot>
    <div class="container py-4"><form method="POST" action="{{ route('estudiantes.update', $estudiante) }}">@csrf @method('PUT') @include('estudiantes._form', ['textoBoton' => 'Actualizar estudiante'])</form></div>
</x-app-layout>
