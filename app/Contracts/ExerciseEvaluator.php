<?php

namespace App\Contracts;

use App\Models\Ejercicio;

interface ExerciseEvaluator
{
    public function evaluate(Ejercicio $exercise, string $answer): bool;
}
