<?php

namespace Tests\Unit;

use App\Models\Ejercicio;
use App\Services\ExerciseEvaluatorResolver;
use InvalidArgumentException;
use Tests\TestCase;

class ExerciseEvaluatorResolverTest extends TestCase
{
    public function test_resuelve_una_estrategia_para_cada_modalidad_registrada(): void
    {
        $resolver = app(ExerciseEvaluatorResolver::class);

        $this->assertEqualsCanonicalizing(array_keys(Ejercicio::modalidades()), $resolver->supportedModalities());

        foreach ($resolver->supportedModalities() as $modality) {
            $this->assertNotNull($resolver->resolve($modality));
        }
    }

    public function test_estrategia_de_opcion_multiple_normaliza_espacios_y_mayusculas(): void
    {
        $exercise = new Ejercicio(['respuesta_correcta' => 'Do - Mi - Sol']);

        $this->assertTrue(app(ExerciseEvaluatorResolver::class)
            ->resolve('clasica')
            ->evaluate($exercise, '  do - MI   - sol '));
    }

    public function test_estrategia_verdadero_falso_acepta_equivalentes_espanol_e_ingles(): void
    {
        $exercise = new Ejercicio(['respuesta_correcta' => 'Verdadero']);

        $this->assertTrue(app(ExerciseEvaluatorResolver::class)
            ->resolve('verdadero_falso')
            ->evaluate($exercise, 'true'));
    }

    public function test_estrategia_de_orden_compara_notas_en_el_mismo_orden(): void
    {
        $exercise = new Ejercicio(['respuesta_correcta' => 'La - Do - Mi - Sol']);
        $evaluator = app(ExerciseEvaluatorResolver::class)->resolve('ordenar_notas');

        $this->assertTrue($evaluator->evaluate($exercise, 'La, Do, Mi, Sol'));
        $this->assertFalse($evaluator->evaluate($exercise, 'Do - La - Mi - Sol'));
    }

    public function test_estrategia_de_intervalo_compara_semitonos_enteros(): void
    {
        $exercise = new Ejercicio(['respuesta_correcta' => '7']);
        $evaluator = app(ExerciseEvaluatorResolver::class)->resolve('seleccionar_intervalo');

        $this->assertTrue($evaluator->evaluate($exercise, '7'));
        $this->assertFalse($evaluator->evaluate($exercise, '7 semitonos'));
    }

    public function test_rechaza_modalidad_sin_estrategia(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(ExerciseEvaluatorResolver::class)->resolve('modalidad_inexistente');
    }
}
