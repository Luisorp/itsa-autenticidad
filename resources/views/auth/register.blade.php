<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Nombre</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="form-control @error('name') is-invalid @enderror">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Correo electrónico</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" class="form-control @error('email') is-invalid @enderror">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input id="password" type="password" name="password" required autocomplete="new-password" class="form-control @error('password') is-invalid @enderror">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="form-control @error('password_confirmation') is-invalid @enderror">
            @error('password_confirmation')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="d-flex align-items-center justify-content-between">
            <a href="{{ route('login') }}" class="small">¿Ya tienes cuenta?</a>
            <button type="submit" class="btn btn-primary">Registrarme</button>
        </div>
    </form>
</x-guest-layout>