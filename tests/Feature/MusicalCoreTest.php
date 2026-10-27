<?php

namespace Tests\Feature;

use App\Models\TipoAcorde;
use App\Models\User;
use App\Services\AcordeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MusicalCoreTest extends TestCase
{
    use RefreshDatabase;

    protected AcordeService $service;
    protected TipoAcorde $tipoMayor;
    protected TipoAcorde $tipoMenor;
    protected TipoAcorde $tipoSiete;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AcordeService();

        $this->tipoMayor = TipoAcorde::create([
            'nombre' => 'Mayor',
            'simbolo' => 'M',
            'intervalos_semitonos' => '[0, 4, 7]',
            'categoria' => 'Tríadas',
        ]);

        $this->tipoMenor = TipoAcorde::create([
            'nombre' => 'Menor',
            'simbolo' => 'm',
            'intervalos_semitonos' => '[0, 3, 7]',
            'categoria' => 'Tríadas',
        ]);

        $this->tipoSiete = TipoAcorde::create([
            'nombre' => 'Séptima dominante',
            'simbolo' => '7',
            'intervalos_semitonos' => '[0, 4, 7, 10]',
            'categoria' => 'Séptimas',
        ]);
    }

    /**
     * Criterio básico de validación 1 del PDF:
     * Al elegir Do mayor, el sistema presenta Do, Mi y Sol.
     */
    public function test_criterio_do_mayor_produce_do_mi_sol(): void
    {
        $resultado = $this->service->construir('Do', $this->tipoMayor);

        $this->assertEquals('Do', $resultado['fundamental']);
        $this->assertEquals(['Do', 'Mi', 'Sol'], $resultado['notas']);
        $this->assertEquals([0, 4, 7], $resultado['semitonos']);
        $this->assertContains(0, $resultado['teclas_activas']);
        $this->assertContains(4, $resultado['teclas_activas']);
        $this->assertContains(7, $resultado['teclas_activas']);
    }

    /**
     * Criterio básico de validación 2 del PDF:
     * Al elegir Do menor, produce Do, Mi bemol (Re#) y Sol.
     */
    public function test_criterio_do_menor_produce_do_re_sostenido_sol(): void
    {
        $resultado = $this->service->construir('Do', $this->tipoMenor);

        $this->assertEquals('Do', $resultado['fundamental']);
        $this->assertEquals(['Do', 'Do#', 'Re', 'Re#', 'Mi', 'Fa', 'Fa#', 'Sol', 'Sol#', 'La', 'La#', 'Si'][3], $resultado['notas'][1]);
        $this->assertEquals('Sol', $resultado['notas'][2]);
        $this->assertEquals([0, 3, 7], $resultado['semitonos']);
    }

    /**
     * Criterio básico de validación 3 del PDF:
     * Al seleccionar un acorde de séptima dominante, devuelve cuatro notas [0, 4, 7, 10].
     */
    public function test_criterio_septima_dominante_produce_cuatro_notas(): void
    {
        $resultado = $this->service->construir('Sol', $this->tipoSiete);

        $this->assertEquals('Sol', $resultado['fundamental']);
        $this->assertCount(4, $resultado['notas']);
        $this->assertEquals(['Sol', 'Si', 'Re', 'Fa'], $resultado['notas']);
        $this->assertEquals([0, 4, 7, 10], $resultado['semitonos']);
    }

    /**
     * Criterio básico de validación 4 del PDF:
     * Si la fundamental o el tipo no existen, devuelve un mensaje de validación sin calcular.
     */
    public function test_validacion_rechaza_parametros_invalidos(): void
    {
        $response = $this->post('/acordes/calcular', [
            'fundamental' => 'NotaInvalida',
            'tipo_id' => 9999,
        ]);

        $response->assertSessionHasErrors(['fundamental', 'tipo_id']);
    }

    /**
     * Prueba que un usuario autenticado puede guardar un acorde en su biblioteca.
     */
    public function test_usuario_puede_guardar_acorde_en_biblioteca(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/acordes/guardar', [
            'fundamental' => 'Do',
            'tipo_id' => $this->tipoMayor->id,
        ])->assertRedirect(route('acordes.biblioteca'));

        $this->assertDatabaseHas('acorde_guardados', [
            'user_id' => $user->id,
            'fundamental' => 'Do',
            'tipo_acorde_id' => $this->tipoMayor->id,
        ]);
    }
}
