<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'ITSa Autenticidad') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="guest-body">
    <main class="guest-shell">
        <section class="guest-identity" aria-label="Sello ITSa" style="--guest-building-url: url('{{ asset('images/edificio-itsa.png') }}')">
            <div class="guest-identity-brand">
                <span class="guest-brand-icon"><i class="bi bi-shield-check"></i></span>
                <div>
                    <span class="guest-eyebrow">Instituto Tecnológico Sacaba</span>
                    <h1>{{ config('app.name', 'Sello ITSa') }}</h1>
                    <p>Autenticidad académica</p>
                </div>
            </div>
            <div class="guest-welcome"><span>Plataforma institucional</span><h2>Conocimiento íntegro,<br>futuro auténtico.</h2><p>Protegemos el valor de cada proyecto de titulación mediante análisis responsable.</p></div>
            <img src="{{ asset('images/edificio-itsa.png') }}" alt="" class="guest-building">
            <span class="guest-institution">Instituto Tecnológico Sacaba · Bolivia</span>
        </section>
        <section class="guest-form-side">
            <div class="guest-card card">
                <div class="guest-form-brand"><span class="guest-brand-icon"><i class="bi bi-shield-check"></i></span><div><strong>Sello ITSa</strong><small>Autenticidad académica</small></div></div>
                <div class="card-body">
                    {{ $slot }}
                </div>
            </div>
            <p class="guest-form-footer"><i class="bi bi-lock"></i> Acceso seguro al sistema institucional</p>
        </section>
    </main>
</body>
</html>
