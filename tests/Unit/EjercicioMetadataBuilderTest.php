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
            'metadata_pista' => 'Escucha la nota.',
            'metadata_tiempo_limite' => '45',
            'metadata_reglas' => 'Usar posición fundamental',
            'metadata_contexto' => 'Tríadas básicas',
            'audio_nota_objetivo' => 'fa#4',
        ];

        $metadata = $builder->build($validated, true);
        $questionData = $builder->buildQuestionData($validated, $metadata);

        $this->assertSame(45, $metadata['tiempo_limite']);
        $this->assertTrue($metadata['audio_captura']);
        $this->assertSame('Fa#4', $metadata['audio_nota_objetivo']);
        $this->assertSame(['Fa#4'], $metadata['audio_notas_objetivo']);
        $this->assertSame(['Mayor', 'Menor'], $questionData['opciones']);
        $this->assertSame('Usar posición fundamental', $questionData['reglas']);
        $this->assertSame('Tríadas básicas', $questionData['contexto']);
    }

    public function test_devuelve_null_cuando_no_hay_metadata(): void
    {
        $builder = new EjercicioMetadataBuilder;

        $this->assertNull($builder->build([], false));
    }

    public function test_convierte_el_formato_anterior_en_una_nota_objetivo(): void
    {
        $metadata = (new EjercicioMetadataBuilder)->build([
            'audio_notas_objetivo' => 'do4, mi4, sol4',
        ], true);

        $this->assertSame('Do4', $metadata['audio_nota_objetivo']);
        $this->assertSame(['Do4'], $metadata['audio_notas_objetivo']);
    }
}
