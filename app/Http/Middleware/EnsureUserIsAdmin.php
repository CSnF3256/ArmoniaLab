<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Asegura que el usuario autenticado posea el rol de administrador o docente.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check() || (! auth()->user()->isAdmin() && ! auth()->user()->isDocente())) {
            abort(403, 'Acceso restringido. Esta sección requiere privilegios de administrador.');
        }

        return $next($request);
    }
}
