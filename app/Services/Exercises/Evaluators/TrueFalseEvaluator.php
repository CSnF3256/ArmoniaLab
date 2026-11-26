<?php

namespace App\Services\Exercises\Evaluators;

use App\Models\Ejercicio;

class TrueFalseEvaluator extends MultipleChoiceEvaluator
{
    public function evaluate(Ejercicio $exercise, string $answer): bool
    {
        $answerValue = $this->normalizeBoolean($answer);
        $expectedValue = $this->normalizeBoolean($exercise->respuesta_correcta);

        return $answerValue !== null && $expectedValue !== null && $answerValue === $expectedValue;
    }

    private function normalizeBoolean(string $value): ?bool
    {
        return match (mb_strtolower(trim($value))) {
            'verdadero', 'true', '1' => true,
            'falso', 'false', '0' => false,
            default => null,
        };
    }
}
