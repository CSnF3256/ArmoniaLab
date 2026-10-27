<?php

namespace App\Http\Controllers;

use App\Services\RecomendacionService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgresoController extends Controller
{
    public function __construct(
        protected RecomendacionService $recomendacionService
    ) {}

    /**
     * Muestra el panel de progreso por habilidad y recomendaciones personalizadas del estudiante.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $diagnostico = $this->recomendacionService->obtenerDiagnostico($user);

        // Historial completo paginado de intentos
        $historialIntentos = $user->intentos()
            ->with('ejercicio.leccion')
            ->latest()
            ->paginate(15);

        // Progreso por lecciones
        $progresosLecciones = $user->progresoLecciones()
            ->with('leccion.course')
            ->get();

        return view('progreso.index', [
            'diagnostico' => $diagnostico,
            'historialIntentos' => $historialIntentos,
            'progresosLecciones' => $progresosLecciones,
        ]);
    }
}
