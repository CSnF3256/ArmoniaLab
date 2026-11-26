<?php

namespace App\Services\Exercises\Evaluators;

use App\Models\Ejercicio;

class IntervalEvaluator extends MultipleChoiceEvaluator
{
    public function evaluate(Ejercicio $exercise, string $answer): bool
    {
        $answerValue = filter_var(trim($answer), FILTER_VALIDATE_INT);
        $expectedValue = filter_var(trim($exercise->respuesta_correcta), FILTER_VALIDATE_INT);

        return $answerValue !== false && $expectedValue !== false && $answerValue === $expectedValue;
    }
}
