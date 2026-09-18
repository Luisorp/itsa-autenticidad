<x-app-layout>
    <x-slot name="header"><h2>Registrar estudiante</h2></x-slot>
    <div class="container py-4"><form method="POST" action="{{ route('estudiantes.store') }}">@csrf @include('estudiantes._form', ['textoBoton' => 'Guardar estudiante'])</form></div>
</x-app-layout>
