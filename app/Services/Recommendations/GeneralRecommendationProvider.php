<?php

namespace App\Services\Recommendations;

use App\Contracts\RecommendationProvider;
use App\Models\Ejercicio;

class GeneralRecommendationProvider implements RecommendationProvider
{
    public function provide(array $weakSkills, int $limit = 4, bool $initialAssessment = false): array
    {
        if ($limit < 1) {
            return [];
        }

        $reason = $initialAssessment
            ? fn (Ejercicio $exercise) => "Evaluación diagnóstica inicial en {$exercise->habilidad}"
            : fn (Ejercicio $exercise) => "Recomendación general para explorar: {$exercise->habilidad}";

        return Ejercicio::with(['leccion.course', 'tipoAcorde'])
            ->take($limit)
            ->get()
            ->map(fn (Ejercicio $exercise) => [
                'ejercicio' => $exercise,
                'motivo' => $reason($exercise),
                'leccion_titulo' => $exercise->leccion?->titulo ?? 'Lección práctica',
                'curso_titulo' => $exercise->leccion?->course?->title ?? 'Curso general',
            ])
            ->all();
    }
}
