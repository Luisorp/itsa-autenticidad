@props(['message'])

<div class="alert alert-success alert-dismissible" role="status" data-success-notice>
    {{ $message }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar mensaje"></button>
</div>
