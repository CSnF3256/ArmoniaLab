<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsStudent
{
    /** El progreso personal pertenece exclusivamente al estudiante. */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isEstudiante()) {
            abort(403, 'Acceso restringido. El progreso personal está disponible para estudiantes.');
        }

        return $next($request);
    }
}
