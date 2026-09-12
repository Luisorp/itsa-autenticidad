<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EsAdministrador
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->esAdministrador()) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}