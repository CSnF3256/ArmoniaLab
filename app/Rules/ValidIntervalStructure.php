<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidIntervalStructure implements ValidationRule
{
    /**
     * Valida en el Back-End que la estructura de intervalos para un tipo de acorde
     * sea teóricamente válida en música:
     * - Debe comenzar obligatoriamente en 0 (fundamental/tónica)
     * - Debe tener al menos 3 intervalos (tríada mínima)
     * - Los semitonos deben ser enteros estrictamente ascendentes
     * - Rango máximo permitido de 24 semitonos (2 octavas)
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $raw = is_array($value) ? implode(',', $value) : (string) $value;
        $cleaned = str_replace(['[', ']', ' '], '', $raw);

        if (trim($cleaned) === '') {
            $fail('La estructura de intervalos no puede estar vacía.');
            return;
        }

        $parts = explode(',', $cleaned);

        foreach ($parts as $part) {
            if (! is_numeric($part) || (int) $part != $part) {
                $fail('Todos los intervalos deben ser valores numéricos enteros en semitonos.');
                return;
            }
        }

        $semitones = array_map('intval', $parts);

        if (count($semitones) < 3) {
            $fail('Un acorde armónico debe contener al menos 3 notas (por ejemplo [0, 4, 7] para una tríada).');
            return;
        }

        if ($semitones[0] !== 0) {
            $fail('La estructura del acorde debe comenzar obligatoriamente en 0 (semitono correspondiente a la tónica o fundamental).');
            return;
        }

        for ($i = 1; $i < count($semitones); $i++) {
            if ($semitones[$i] <= $semitones[$i - 1]) {
                $fail('Los intervalos en semitonos deben ser estrictamente crecientes (ej: 0, 4, 7).');
                return;
            }

            if ($semitones[$i] > 24) {
                $fail("El intervalo {$semitones[$i]} excede el límite armónico de 2 octavas (máximo 24 semitonos).");
                return;
            }
        }
    }
}
