<x-guest-layout>
    <div class="login-heading"><span>Acceso institucional</span><h1>Bienvenido de nuevo</h1><p>Ingresa tus datos para continuar.</p></div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Correo electrónico</label>
            <div class="login-input"><i class="bi bi-envelope"></i><input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="usuario@itsa.edu.bo" class="form-control @error('email') is-invalid @enderror"></div>
            @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3" x-data="{ visible: false }">
            <label for="password" class="form-label">Contraseña</label>
            <div class="login-input institutional-password"><i class="bi bi-lock"></i><input id="password" type="password" :type="visible ? 'text' : 'password'" name="password" required autocomplete="current-password" placeholder="Ingresa tu contraseña" class="form-control @error('password') is-invalid @enderror"><button type="button" class="password-visibility" @click="visible = !visible" :aria-label="visible ? 'Ocultar contraseña' : 'Mostrar contraseña'" :aria-pressed="visible.toString()" aria-label="Mostrar contraseña" aria-controls="password"><i class="bi bi-eye" :class="{ 'bi-eye': !visible, 'bi-eye-slash': visible }" aria-hidden="true"></i></button></div>
            @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>

        <div class="login-options mb-3"><label class="form-check"><input type="checkbox" name="remember" id="remember" class="form-check-input"><span class="form-check-label">Recordarme</span></label>
        </div>

        <button type="submit" class="btn institutional-login-submit"><span>Iniciar sesión</span><i class="bi bi-arrow-right" aria-hidden="true"></i></button>
    </form>
</x-guest-layout>
