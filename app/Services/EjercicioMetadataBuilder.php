<?php

namespace App\Services;

final class EjercicioMetadataBuilder
{
    /** @param array<string, mixed> $validated */
    public function build(array $validated, bool $audioEnabled): ?array
    {
        $audioTargetNote = $audioEnabled
            ? $this->resolveAudioTargetNote($validated)
            : null;

        $metadata = array_filter([
            'pista' => $validated['metadata_pista'] ?? null,
            'tiempo_limite' => isset($validated['metadata_tiempo_limite'])
                ? (int) $validated['metadata_tiempo_limite']
                : null,
            'reglas' => $validated['metadata_reglas'] ?? null,
            'contexto' => $validated['metadata_contexto'] ?? null,
            'audio_captura' => $audioEnabled ? true : null,
            'audio_nota_objetivo' => $audioTargetNote,
            // Puente temporal para el componente de audio anterior.
            'audio_notas_objetivo' => $audioTargetNote !== null ? [$audioTargetNote] : null,
        ], fn (mixed $value): bool => $value !== null && $value !== '');

        return $metadata === [] ? null : $metadata;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @param  array<string, mixed>|null  $metadata
     * @return array<string, mixed>
     */
    public function buildQuestionData(array $validated, ?array $metadata): array
    {
        $questionData = [
            'opciones' => array_values(array_filter([
                $validated['opcion_1'] ?? null,
                $validated['opcion_2'] ?? null,
                $validated['opcion_3'] ?? null,
                $validated['opcion_4'] ?? null,
            ], fn (mixed $value): bool => $value !== null && $value !== '')),
        ];

        foreach (['reglas', 'contexto'] as $field) {
            if (! empty($metadata[$field])) {
                $questionData[$field] = $metadata[$field];
            }
        }

        return $questionData;
    }

    /** @return array<int, string> */
    public function normalizeAudioNotes(string $value): array
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

    /** @param array<string, mixed> $validated */
    private function resolveAudioTargetNote(array $validated): ?string
    {
        $value = (string) ($validated['audio_nota_objetivo']
            ?? $validated['audio_notas_objetivo']
            ?? '');

        return $this->normalizeAudioNotes($value)[0] ?? null;
    }
}
