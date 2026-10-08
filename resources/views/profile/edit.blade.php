<x-app-layout>
    <x-slot name="header">
        <div class="page-title"><h2>Mi perfil</h2><span>Administra tus datos de acceso y tu contraseña.</span></div>
    </x-slot>

    <div class="container py-3">
        @if(session('status') === 'profile-updated')
            <x-flash-success message="Tus datos se guardaron correctamente." />
        @elseif(session('status') === 'password-updated')
            <x-flash-success message="Tu contraseña se actualizó correctamente." />
        @elseif(session('status') === 'verification-link-sent')
            <x-flash-success message="Se ha enviado un nuevo enlace de verificación a tu correo." />
        @endif

        <div class="row g-4 align-items-start">
            <div class="col-12 col-xl-6">
                <div class="card"><div class="card-body">
                    @include('profile.partials.update-profile-information-form')
                </div></div>
            </div>
            <div class="col-12 col-xl-6">
                <div class="card"><div class="card-body">
                    @include('profile.partials.update-password-form')
                </div></div>
            </div>
        </div>
    </div>
</x-app-layout>
