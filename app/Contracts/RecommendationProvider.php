<?php

namespace App\Contracts;

interface RecommendationProvider
{
    /** @return list<array{ejercicio: \App\Models\Ejercicio, motivo: string, leccion_titulo: string, curso_titulo: string}> */
    public function provide(array $weakSkills, int $limit = 4): array;
}
