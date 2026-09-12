<x-guest-layout>
    <div class="login-heading"><span>Bienvenido</span><h2>Inicia sesión</h2><p>Accede al sistema de análisis de proyectos de titulación.</p></div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Correo electrónico</label>
            <div class="login-input"><i class="bi bi-envelope"></i><input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="usuario@itsa.edu.bo" class="form-control @error('email') is-invalid @enderror"></div>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <div class="login-input"><i class="bi bi-lock"></i><input id="password" type="password" name="password" required autocomplete="current-password" placeholder="Ingresa tu contraseña" class="form-control @error('password') is-invalid @enderror"></div>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="login-options mb-3"><label class="form-check"><input type="checkbox" name="remember" id="remember" class="form-check-input"><span class="form-check-label">Recordarme</span></label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="small">¿Olvidaste tu contraseña?</a>
            @endif
        </div>

        <button type="submit" class="btn btn-primary login-submit"><i class="bi bi-box-arrow-in-right"></i> Iniciar sesión</button>
    </form>
</x-guest-layout>
