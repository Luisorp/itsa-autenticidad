<section aria-labelledby="profile-information-title">
    <header class="mb-4">
        <h3 id="profile-information-title" class="h5"><i class="bi bi-person-circle me-2" aria-hidden="true"></i>Datos de mi cuenta</h3>
        <p class="text-muted mb-0">Actualiza tu nombre y el correo que utilizas para iniciar sesión.</p>
    </header>

    <form id="send-verification" method="POST" action="{{ route('verification.send') }}">@csrf</form>

    <form method="POST" action="{{ route('profile.update') }}">
        @csrf
        @method('PATCH')
        <div class="mb-3">
            <label class="form-label" for="name">Nombre completo</label>
            <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required maxlength="255" autocomplete="name" @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
            @error('name')<div id="name-error" class="invalid-feedback" role="alert">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label class="form-label" for="email">Correo de acceso</label>
            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required maxlength="255" autocomplete="username" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
            @error('email')<div id="email-error" class="invalid-feedback" role="alert">{{ $message }}</div>@enderror
            <div class="form-text">Si lo cambias, utiliza el nuevo correo para iniciar sesión. El envío de correos y la recuperación por correo no están habilitados por el momento.</div>
        </div>

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="alert alert-warning">
                <p class="mb-2">Tu correo electrónico todavía no está verificado.</p>
                <button type="submit" form="send-verification" class="btn btn-outline-primary">Reenviar verificación</button>
            </div>
        @endif

        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1" aria-hidden="true"></i>Guardar cambios</button>
    </form>
</section>
