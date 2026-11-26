<?php

namespace App\Services\Recommendations;

class RecommendationFallbackResolver
{
    public function __construct(
        private readonly PersonalizedRecommendationProvider $personalizedProvider,
        private readonly GeneralRecommendationProvider $generalProvider,
    ) {}

    /** @return list<array{ejercicio: \App\Models\Ejercicio, motivo: string, leccion_titulo: string, curso_titulo: string}> */
    public function recommend(array $weakSkills, int $limit = 4, bool $initialAssessment = false): array
    {
        $personalized = $this->personalizedProvider->provide($weakSkills, $limit, $initialAssessment);

        if ($personalized !== []) {
            return $personalized;
        }

        return $this->generalProvider->provide($weakSkills, $limit, $initialAssessment);
    }
}
