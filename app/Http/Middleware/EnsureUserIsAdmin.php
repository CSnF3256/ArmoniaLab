<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /** Asegura que solo un administrador pueda continuar. */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isAdmin()) {
            abort(403, 'Acceso restringido. Esta sección requiere privilegios de administrador.');
        }

        return $next($request);
    }
}
