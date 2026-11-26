<?php

namespace App\Services\Exercises\Evaluators;

use App\Models\Ejercicio;

class NoteOrderEvaluator extends MultipleChoiceEvaluator
{
    public function evaluate(Ejercicio $exercise, string $answer): bool
    {
        return $this->normalizeSequence($answer) === $this->normalizeSequence($exercise->respuesta_correcta);
    }

    private function normalizeSequence(string $sequence): array
    {
        $notes = preg_split('/\s*(?:-|–|—|,|;|>|→)\s*/u', trim($sequence), -1, PREG_SPLIT_NO_EMPTY);

        return array_map(fn (string $note) => mb_strtolower(trim($note)), $notes ?: []);
    }
}
