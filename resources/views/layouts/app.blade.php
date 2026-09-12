<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Sello ITSa') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-body">
    <div class="app-shell d-flex">
        @include('layouts.navigation')

        <div class="flex-grow-1 main-content">
            @isset($header)
                <div class="page-header">
                    <div class="container-fluid d-flex justify-content-between align-items-center">
                        <div>{{ $header }}</div>
                        <div class="header-account">
                            <span class="header-date">{{ now()->locale('es')->translatedFormat('l, d \d\e F \d\e Y') }}</span>
                            <div class="user-role-badge">
                                <i class="bi bi-person-circle"></i>
                                <span><strong>{{ Auth::user()->name }}</strong><small>{{ ucfirst(Auth::user()->rol) }}</small></span>
                            </div>
                        </div>
                    </div>
                </div>
            @endisset

            <main class="container-fluid app-main">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
