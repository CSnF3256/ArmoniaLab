<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanTeach
{
    /** Permite gestionar contenido a docentes y administradores. */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isDocente()) {
            abort(403, 'Acceso restringido. Esta acción requiere el rol de docente o administrador.');
        }

        return $next($request);
    }
}
