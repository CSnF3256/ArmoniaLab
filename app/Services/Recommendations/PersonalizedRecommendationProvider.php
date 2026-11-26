<?php

namespace App\Services\Recommendations;

use App\Contracts\RecommendationProvider;
use App\Models\Ejercicio;

class PersonalizedRecommendationProvider implements RecommendationProvider
{
    public function provide(array $weakSkills, int $limit = 4): array
    {
        if ($weakSkills === [] || $limit < 1) {
            return [];
        }

        return Ejercicio::with(['leccion.course', 'tipoAcorde'])
            ->whereIn('habilidad', $weakSkills)
            ->take($limit)
            ->get()
            ->map(fn (Ejercicio $exercise) => [
                'ejercicio' => $exercise,
                'motivo' => "Práctica personalizada para reforzar: {$exercise->habilidad}",
                'leccion_titulo' => $exercise->leccion?->titulo ?? 'Lección práctica',
                'curso_titulo' => $exercise->leccion?->course?->title ?? 'Curso general',
            ])
            ->all();
    }
}
