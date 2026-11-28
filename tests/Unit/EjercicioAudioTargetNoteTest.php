<?php

namespace Tests\Unit;

use App\Models\Ejercicio;
use PHPUnit\Framework\TestCase;

class EjercicioAudioTargetNoteTest extends TestCase
{
    public function test_lee_la_nueva_nota_objetivo(): void
    {
        $ejercicio = new Ejercicio([
            'metadata' => ['audio_nota_objetivo' => 'La4'],
        ]);

        $this->assertSame('La4', $ejercicio->audioTargetNote());
    }

    public function test_usa_la_primera_nota_de_metadata_anterior(): void
    {
        $ejercicio = new Ejercicio([
            'metadata' => ['audio_notas_objetivo' => ['Do4', 'Mi4', 'Sol4']],
        ]);

        $this->assertSame('Do4', $ejercicio->audioTargetNote());
    }

    public function test_la_nueva_nota_tiene_prioridad_sobre_metadata_anterior(): void
    {
        $ejercicio = new Ejercicio([
            'metadata' => [
                'audio_nota_objetivo' => 'Re4',
                'audio_notas_objetivo' => ['Do4'],
            ],
        ]);

        $this->assertSame('Re4', $ejercicio->audioTargetNote());
    }
}
