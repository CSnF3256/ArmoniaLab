<?php

namespace Tests\Unit;

use App\Services\EjercicioMetadataBuilder;
use PHPUnit\Framework\TestCase;

class EjercicioMetadataBuilderTest extends TestCase
{
    public function test_construye_metadata_y_datos_de_pregunta_normalizados(): void
    {
        $builder = new EjercicioMetadataBuilder;
        $validated = [
            'opcion_1' => 'Mayor',
            'opcion_2' => 'Menor',
            'opcion_3' => '',
            'metadata_pista' => 'Escucha las tres notas.',
            'metadata_tiempo_limite' => '45',
            'metadata_reglas' => 'Usar posición fundamental',
            'metadata_contexto' => 'Tríadas básicas',
            'audio_notas_objetivo' => 'do4, FA#4, sib4',
        ];

        $metadata = $builder->build($validated, true);
        $questionData = $builder->buildQuestionData($validated, $metadata);

        $this->assertSame(45, $metadata['tiempo_limite']);
        $this->assertTrue($metadata['audio_captura']);
        $this->assertSame(['Do4', 'Fa#4', 'Sib4'], $metadata['audio_notas_objetivo']);
        $this->assertSame(['Mayor', 'Menor'], $questionData['opciones']);
        $this->assertSame('Usar posición fundamental', $questionData['reglas']);
        $this->assertSame('Tríadas básicas', $questionData['contexto']);
    }

    public function test_devuelve_null_cuando_no_hay_metadata(): void
    {
        $builder = new EjercicioMetadataBuilder;

        $this->assertNull($builder->build([], false));
    }
}
