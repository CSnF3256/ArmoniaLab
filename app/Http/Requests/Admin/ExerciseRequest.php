<?php

namespace App\Http\Requests\Admin;

use App\Models\Ejercicio;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class ExerciseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'leccion_id' => [
                'required',
                'integer',
                Rule::exists('lecciones', 'id')
                    ->where(fn ($query) => $query->where('course_id', $this->input('course_id'))),
            ],
            'tipo_acorde_id' => ['nullable', 'integer', 'exists:tipo_acordes,id'],
            'pregunta' => ['required', 'string'],
            'tipo' => ['required', 'string', 'in:opcion_multiple,construir_acorde,identificar_notas,intervalo'],
            'modalidad' => ['required', 'string', Rule::in(array_keys(Ejercicio::modalidades()))],
            'habilidad' => ['required', 'string', 'max:100'],
            'dificultad' => ['required', 'string', 'in:Fácil,Medio,Avanzado'],
            'opcion_1' => ['required', 'string'],
            'opcion_2' => ['required', 'string'],
            'opcion_3' => ['nullable', 'string'],
            'opcion_4' => ['nullable', 'string'],
            'respuesta_correcta' => ['required', 'string'],
            'explicacion' => ['nullable', 'string'],
            'puntos' => ['required', 'integer', 'min:1', 'max:100'],
            'metadata_pista' => ['nullable', 'string', 'max:255'],
            'metadata_tiempo_limite' => ['nullable', 'integer', 'min:0', 'max:600'],
            'metadata_reglas' => ['nullable', 'string', 'max:500'],
            'metadata_contexto' => ['nullable', 'string', 'max:500'],
            'audio_captura' => ['nullable', 'boolean'],
            'audio_notas_objetivo' => [
                Rule::requiredIf($this->boolean('audio_captura')),
                'nullable',
                'string',
                'max:100',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! $this->audioNotesAreValid((string) $value)) {
                        $fail('Las notas objetivo deben incluir octava y estar separadas por comas, por ejemplo Do4, Fa#4 o Sib3.');
                    }
                },
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'course_id.required' => 'Debe seleccionar un curso desde el dropdown.',
            'leccion_id.required' => 'Debe seleccionar una lección dependiente del curso.',
            'leccion_id.exists' => 'La lección elegida no pertenece al curso seleccionado.',
            'respuesta_correcta.required' => 'La respuesta correcta es obligatoria.',
            'modalidad.required' => 'Debe elegir una modalidad de ejercicio.',
            'modalidad.in' => 'La modalidad seleccionada no es válida.',
        ];
    }

    private function audioNotesAreValid(string $value): bool
    {
        $notes = $this->normalizeAudioNotes($value);
        $pitchClasses = [
            'Do' => 0, 'Do#' => 1, 'Reb' => 1, 'Re' => 2, 'Re#' => 3, 'Mib' => 3,
            'Mi' => 4, 'Fa' => 5, 'Fa#' => 6, 'Solb' => 6, 'Sol' => 7,
            'Sol#' => 8, 'Lab' => 8, 'La' => 9, 'La#' => 10, 'Sib' => 10, 'Si' => 11,
        ];
        $frequencies = [];

        foreach ($notes as $note) {
            if (! preg_match('/^(.+)([2-6])$/u', $note, $match) || ! isset($pitchClasses[$match[1]])) {
                return false;
            }

            $frequencies[] = $pitchClasses[$match[1]].':'.$match[2];
        }

        return count($notes) >= 2
            && count($notes) <= 6
            && count($frequencies) === count(array_unique($frequencies));
    }

    /** @return array<int, string> */
    private function normalizeAudioNotes(string $value): array
    {
        $names = [
            'do' => 'Do', 'do#' => 'Do#', 'reb' => 'Reb', 're' => 'Re', 're#' => 'Re#', 'mib' => 'Mib',
            'mi' => 'Mi', 'fa' => 'Fa', 'fa#' => 'Fa#', 'solb' => 'Solb', 'sol' => 'Sol',
            'sol#' => 'Sol#', 'lab' => 'Lab', 'la' => 'La', 'la#' => 'La#', 'sib' => 'Sib', 'si' => 'Si',
        ];

        return array_values(array_filter(array_map(function (string $note) use ($names): string {
            $note = strtolower(trim($note));

            if (preg_match('/^([a-z]+#?)([2-6])$/u', $note, $match) && isset($names[$match[1]])) {
                return $names[$match[1]].$match[2];
            }

            return ucfirst($note);
        }, explode(',', $value))));
    }
}
