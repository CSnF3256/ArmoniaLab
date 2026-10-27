<?php

namespace Tests\Feature;

use App\Models\Ciudad;
use App\Models\Pais;
use App\Models\Provincia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackendValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Pais $pais;
    protected Provincia $provincia;
    protected Ciudad $ciudad;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pais = Pais::create(['nombre' => 'Ecuador', 'codigo' => 'EC']);
        $this->provincia = Provincia::create(['pais_id' => $this->pais->id, 'nombre' => 'Pichincha', 'codigo' => '17']);
        $this->ciudad = Ciudad::create(['provincia_id' => $this->provincia->id, 'nombre' => 'Quito']);

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    /**
     * Prueba que el Back-End ACEPTA una cédula ecuatoriana válida con Módulo 10.
     */
    public function test_backend_acepta_cedula_ecuatoriana_valida(): void
    {
        // Cédula válida real de Pichincha: 1710034065
        $response = $this->actingAs($this->admin)->post('/admin/usuarios', [
            'name' => 'Juan Pérez',
            'email' => 'juan.perez@udla.edu.ec',
            'password' => 'secret1234',
            'role' => 'estudiante',
            'cedula' => '1710034065',
            'pais_id' => $this->pais->id,
            'provincia_id' => $this->provincia->id,
            'ciudad_id' => $this->ciudad->id,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', [
            'email' => 'juan.perez@udla.edu.ec',
            'cedula' => '1710034065',
        ]);
    }

    /**
     * Prueba que el Back-End RECHAZA una cédula con dígito verificador inválido.
     */
    public function test_backend_rechaza_cedula_con_digito_verificador_invalido(): void
    {
        // 1710034069 tiene dígito verificador 9 en vez de 5
        $response = $this->actingAs($this->admin)->post('/admin/usuarios', [
            'name' => 'Usuario Falso',
            'email' => 'falso@udla.edu.ec',
            'password' => 'secret1234',
            'role' => 'estudiante',
            'cedula' => '1710034069',
            'pais_id' => $this->pais->id,
            'provincia_id' => $this->provincia->id,
            'ciudad_id' => $this->ciudad->id,
        ]);

        $response->assertSessionHasErrors(['cedula']);
        $this->assertDatabaseMissing('users', ['email' => 'falso@udla.edu.ec']);
    }

    /**
     * Prueba que el Back-End RECHAZA cédula con código de provincia inexistente (ej: 99).
     */
    public function test_backend_rechaza_cedula_con_provincia_invalida(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/usuarios', [
            'name' => 'Provincia Invalida',
            'email' => 'provincia@udla.edu.ec',
            'password' => 'secret1234',
            'role' => 'estudiante',
            'cedula' => '9912345678',
            'pais_id' => $this->pais->id,
            'provincia_id' => $this->provincia->id,
            'ciudad_id' => $this->ciudad->id,
        ]);

        $response->assertSessionHasErrors(['cedula']);
        $this->assertDatabaseMissing('users', ['email' => 'provincia@udla.edu.ec']);
    }

    /**
     * Prueba que el Back-End ACEPTA una fórmula de intervalos válida para tipos de acorde.
     */
    public function test_backend_acepta_formula_intervalos_valida(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/tipos-acordes', [
            'nombre' => 'Novena mayor',
            'simbolo' => 'maj9',
            'categoria' => 'Extendidos',
            'intervalos_semitonos' => '0, 4, 7, 11, 14',
            'descripcion' => 'Acorde extendido con novena mayor.',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tipo_acordes', [
            'nombre' => 'Novena mayor',
            'intervalos_semitonos' => '[0, 4, 7, 11, 14]',
        ]);
    }

    /**
     * Prueba que el Back-End RECHAZA fórmulas que no inicien en 0 (tónica obligatoria).
     */
    public function test_backend_rechaza_formula_que_no_inicia_en_cero(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/tipos-acordes', [
            'nombre' => 'Acorde Roto',
            'simbolo' => 'roto',
            'categoria' => 'Tríadas',
            'intervalos_semitonos' => '2, 4, 7',
        ]);

        $response->assertSessionHasErrors(['intervalos_semitonos']);
        $this->assertDatabaseMissing('tipo_acordes', ['nombre' => 'Acorde Roto']);
    }

    /**
     * Prueba que el Back-End RECHAZA fórmulas no crecientes o con menos de 3 notas.
     */
    public function test_backend_rechaza_formula_no_creciente_o_insuficiente(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/tipos-acordes', [
            'nombre' => 'Acorde Desordenado',
            'simbolo' => 'des',
            'categoria' => 'Tríadas',
            'intervalos_semitonos' => '0, 7, 4',
        ]);

        $response->assertSessionHasErrors(['intervalos_semitonos']);
    }
}
