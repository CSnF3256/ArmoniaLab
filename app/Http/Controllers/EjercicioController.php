<?php

namespace App\Http\Controllers;

use App\Models\Ejercicio;
use App\Models\Intento;
use App\Models\ProgresoLeccion;
use App\Services\RecomendacionService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EjercicioController extends Controller
{
    public function __construct(
        protected RecomendacionService $recomendacionService
    ) {}

    /**
     * Muestra el catálogo de ejercicios interactivos disponibles.
     */
    public function index(Request $request): View
    {
        $habilidadFiltro = $request->input('habilidad');

        $query = Ejercicio::with(['leccion.course', 'tipoAcorde'])->latest();
        if ($habilidadFiltro) {
            $query->where('habilidad', $habilidadFiltro);
        }

        $ejercicios = $query->paginate(9);

        // Habilidades únicas para filtro
        $habilidades = Ejercicio::distinct()->pluck('habilidad');

        // Intentos previos del usuario actual
        $intentosUsuario = auth()->user()->intentos()
            ->latest()
            ->get()
            ->groupBy('ejercicio_id');

        // Diagnóstico rápido de recomendación
        $diagnostico = $this->recomendacionService->obtenerDiagnostico(auth()->user());

        return view('ejercicios.index', [
            'ejercicios' => $ejercicios,
            'habilidades' => $habilidades,
            'habilidadSeleccionada' => $habilidadFiltro,
            'intentosUsuario' => $intentosUsuario,
            'diagnostico' => $diagnostico,
        ]);
    }

    /**
     * Pantalla interactiva para resolver el ejercicio.
     */
    public function show(Ejercicio $ejercicio): View
    {
        $ejercicio->load(['leccion.course', 'tipoAcorde']);

        $ultimosIntentos = auth()->user()->intentos()
            ->where('ejercicio_id', $ejercicio->id)
            ->latest()
            ->take(5)
            ->get();

        return view('ejercicios.show', [
            'ejercicio' => $ejercicio,
            'ultimosIntentos' => $ultimosIntentos,
        ]);
    }

    /**
     * Corrección automática en el Back-End y registro del intento con progreso.
     */
    public function responder(Request $request, Ejercicio $ejercicio)
    {
        $request->validate([
            'respuesta' => ['required', 'string'],
            'tiempo_segundos' => ['nullable', 'integer'],
        ], [
            'respuesta.required' => 'Debes seleccionar o ingresar una respuesta antes de enviar.',
        ]);

        $respuestaUsuario = trim($request->input('respuesta'));
        $respuestaCorrecta = trim($ejercicio->respuesta_correcta);

        // Normalización para comparación insensible a mayúsculas y espacios duplicados
        $esCorrecta = mb_strtolower(preg_replace('/\s+/', ' ', $respuestaUsuario)) ===
                      mb_strtolower(preg_replace('/\s+/', ' ', $respuestaCorrecta));

        $puntos = $esCorrecta ? $ejercicio->puntos : 0;
        $explicacion = $esCorrecta
            ? '¡Respuesta correcta! ' . ($ejercicio->explicacion ?? 'Has aplicado correctamente las reglas armónicas.')
            : 'Respuesta incorrecta. ' . ($ejercicio->explicacion ?? "La respuesta esperada era: {$respuestaCorrecta}.");

        // 1. Registrar el intento
        $intento = Intento::create([
            'user_id' => auth()->id(),
            'ejercicio_id' => $ejercicio->id,
            'respuesta_usuario' => $respuestaUsuario,
            'es_correcta' => $esCorrecta,
            'puntaje_obtenido' => $puntos,
            'explicacion_resultado' => $explicacion,
            'tiempo_segundos' => $request->integer('tiempo_segundos', null),
            'habilidad' => $ejercicio->habilidad,
        ]);

        // 2. Actualizar el progreso de la lección
        if ($ejercicio->leccion_id) {
            $progreso = ProgresoLeccion::firstOrCreate(
                [
                    'user_id' => auth()->id(),
                    'leccion_id' => $ejercicio->leccion_id,
                ],
                [
                    'estatus' => 'en_progreso',
                    'intentos_totales' => 0,
                    'intentos_correctos' => 0,
                ]
            );

            $progreso->increment('intentos_totales');
            if ($esCorrecta) {
                $progreso->increment('intentos_correctos');
                $progreso->estatus = 'completada';
                $progreso->fecha_completado = now();
                $progreso->save();
            }
        }

        // Buscar siguiente ejercicio
        $siguienteEjercicio = Ejercicio::where('id', '>', $ejercicio->id)->first();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'es_correcta' => $esCorrecta,
                'explicacion' => $explicacion,
                'respuesta_correcta' => $respuestaCorrecta,
                'puntos' => $puntos,
                'siguiente_id' => $siguienteEjercicio?->id,
            ]);
        }

        return redirect()->route('ejercicios.show', $ejercicio)
            ->with($esCorrecta ? 'resultado_exito' : 'resultado_error', $explicacion);
    }
}
