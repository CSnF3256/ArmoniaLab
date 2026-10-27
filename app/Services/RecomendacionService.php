<?php

namespace App\Services;

use App\Models\Ejercicio;
use App\Models\Intento;
use App\Models\User;

class RecomendacionService
{
    /**
     * Analiza el historial de intentos del usuario y genera un diagnóstico
     * detallado de progreso por habilidad con recomendaciones personalizadas.
     */
    public function obtenerDiagnostico(User $user): array
    {
        $intentos = $user->intentos()->with('ejercicio.leccion')->latest()->get();

        if ($intentos->isEmpty()) {
            return [
                'total_intentos' => 0,
                'total_correctos' => 0,
                'porcentaje_global' => 0,
                'habilidades' => [],
                'habilidades_criticas' => [],
                'habilidades_dominadas' => [],
                'recomendaciones' => $this->generarRecomendacionesIniciales(),
                'mensaje_diagnostico' => 'Aún no has resuelto ejercicios. Comienza con una práctica guiada para evaluar tu nivel armónico.',
            ];
        }

        $totalIntentos = $intentos->count();
        $totalCorrectos = $intentos->where('es_correcta', true)->count();
        $porcentajeGlobal = round(($totalCorrectos / $totalIntentos) * 100, 1);

        // Agrupar intentos por habilidad
        $habilidadesData = [];
        foreach ($intentos as $intento) {
            $habilidad = $intento->habilidad ?: ($intento->ejercicio?->habilidad ?: 'Fundamentos Musicales');
            if (! isset($habilidadesData[$habilidad])) {
                $habilidadesData[$habilidad] = [
                    'nombre' => $habilidad,
                    'totales' => 0,
                    'correctos' => 0,
                    'fallidos' => 0,
                    'ultimo_intento' => $intento->created_at,
                    'ejercicios_fallados_ids' => [],
                ];
            }

            $habilidadesData[$habilidad]['totales']++;
            if ($intento->es_correcta) {
                $habilidadesData[$habilidad]['correctos']++;
            } else {
                $habilidadesData[$habilidad]['fallidos']++;
                if ($intento->ejercicio_id) {
                    $habilidadesData[$habilidad]['ejercicios_fallados_ids'][] = $intento->ejercicio_id;
                }
            }
        }

        $habilidades = [];
        $habilidadesCriticas = [];
        $habilidadesDominadas = [];

        foreach ($habilidadesData as $nombre => $datos) {
            $porcentaje = round(($datos['correctos'] / $datos['totales']) * 100, 1);
            $estado = match (true) {
                $porcentaje >= 80 => 'Dominado',
                $porcentaje >= 60 => 'En Progreso',
                default => 'Requiere Refuerzo',
            };

            $info = [
                'nombre' => $nombre,
                'totales' => $datos['totales'],
                'correctos' => $datos['correctos'],
                'fallidos' => $datos['fallidos'],
                'porcentaje' => $porcentaje,
                'estado' => $estado,
                'ejercicios_fallados' => array_values(array_unique($datos['ejercicios_fallados_ids'])),
            ];

            $habilidades[$nombre] = $info;

            if ($porcentaje < 70) {
                $habilidadesCriticas[] = $info;
            } else {
                $habilidadesDominadas[] = $info;
            }
        }

        $recomendaciones = $this->proponerEjerciciosPersonalizados($user, $habilidadesCriticas);

        $mensajeDiagnostico = empty($habilidadesCriticas)
            ? '¡Excelente rendimiento armónico! Has dominado la mayoría de habilidades evaluadas. Sigue con desafíos avanzados.'
            : 'Hemos detectado oportunidades de mejora en habilidades clave. Te recomendamos enfocarte en las siguientes prácticas:';

        return [
            'total_intentos' => $totalIntentos,
            'total_correctos' => $totalCorrectos,
            'porcentaje_global' => $porcentajeGlobal,
            'habilidades' => $habilidades,
            'habilidades_criticas' => $habilidadesCriticas,
            'habilidades_dominadas' => $habilidadesDominadas,
            'recomendaciones' => $recomendaciones,
            'mensaje_diagnostico' => $mensajeDiagnostico,
        ];
    }

    /**
     * Propone ejercicios específicos basados en las habilidades débiles del usuario
     * o ejercicios previamente fallados.
     */
    public function proponerEjerciciosPersonalizados(User $user, array $habilidadesCriticas, int $limite = 4): array
    {
        $nombresCriticos = array_column($habilidadesCriticas, 'nombre');

        $query = Ejercicio::with(['leccion.course', 'tipoAcorde']);

        if (! empty($nombresCriticos)) {
            $query->whereIn('habilidad', $nombresCriticos);
        }

        $ejercicios = $query->take($limite)->get();

        if ($ejercicios->isEmpty()) {
            $ejercicios = Ejercicio::with(['leccion.course', 'tipoAcorde'])->take($limite)->get();
        }

        return $ejercicios->map(function (Ejercicio $ej) {
            return [
                'ejercicio' => $ej,
                'motivo' => "Práctica personalizada para reforzar: {$ej->habilidad}",
                'leccion_titulo' => $ej->leccion?->titulo ?? 'Lección práctica',
                'curso_titulo' => $ej->leccion?->course?->title ?? 'Curso general',
            ];
        })->toArray();
    }

    /**
     * Recomendaciones por defecto para usuarios sin historial.
     */
    protected function generarRecomendacionesIniciales(): array
    {
        $ejercicios = Ejercicio::with(['leccion.course', 'tipoAcorde'])->take(3)->get();

        return $ejercicios->map(function (Ejercicio $ej) {
            return [
                'ejercicio' => $ej,
                'motivo' => "Evaluación diagnóstica inicial en {$ej->habilidad}",
                'leccion_titulo' => $ej->leccion?->titulo ?? 'Lección práctica',
                'curso_titulo' => $ej->leccion?->course?->title ?? 'Curso general',
            ];
        })->toArray();
    }
}
