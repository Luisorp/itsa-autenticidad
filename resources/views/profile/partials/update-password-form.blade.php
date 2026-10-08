<section aria-labelledby="profile-password-title">
    <header class="mb-4">
        <h3 id="profile-password-title" class="h5"><i class="bi bi-shield-lock me-2" aria-hidden="true"></i>Cambiar contraseña</h3>
        <p class="text-muted mb-0">Para proteger tu cuenta, confirma tu contraseña actual antes de cambiarla.</p>
    </header>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label" for="update_password_current_password">Contraseña actual</label>
            <input id="update_password_current_password" name="current_password" type="password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" required autocomplete="current-password" @error('current_password', 'updatePassword') aria-invalid="true" aria-describedby="current-password-error" @enderror>
            @error('current_password', 'updatePassword')<div id="current-password-error" class="invalid-feedback" role="alert">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="update_password_password">Nueva contraseña</label>
            <input id="update_password_password" name="password" type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" required autocomplete="new-password" aria-describedby="password-help @error('password', 'updatePassword') password-error @enderror" @error('password', 'updatePassword') aria-invalid="true" @enderror>
            <div id="password-help" class="form-text">Usa al menos 8 caracteres. Evita nombres y datos fáciles de adivinar.</div>
            @error('password', 'updatePassword')<div id="password-error" class="invalid-feedback" role="alert">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label class="form-label" for="update_password_password_confirmation">Confirmar nueva contraseña</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" required autocomplete="new-password" @error('password_confirmation', 'updatePassword') aria-invalid="true" aria-describedby="password-confirmation-error" @enderror>
            @error('password_confirmation', 'updatePassword')<div id="password-confirmation-error" class="invalid-feedback" role="alert">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-primary"><i class="bi bi-key me-1" aria-hidden="true"></i>Actualizar contraseña</button>
    </form>
</section>
