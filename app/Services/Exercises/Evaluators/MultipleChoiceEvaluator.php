<?php

namespace App\Services\Exercises\Evaluators;

use App\Contracts\ExerciseEvaluator;
use App\Models\Ejercicio;

class MultipleChoiceEvaluator implements ExerciseEvaluator
{
    public function evaluate(Ejercicio $exercise, string $answer): bool
    {
        return $this->normalize($answer) === $this->normalize($exercise->respuesta_correcta);
    }

    protected function normalize(string $value): string
    {
        return mb_strtolower((string) preg_replace('/\s+/u', ' ', trim($value)));
    }
}
