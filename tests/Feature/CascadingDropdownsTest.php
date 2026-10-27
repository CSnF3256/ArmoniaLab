<?php

namespace Tests\Feature;

use App\Models\Ciudad;
use App\Models\Course;
use App\Models\Leccion;
use App\Models\Pais;
use App\Models\Provincia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CascadingDropdownsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Pais $paisEcuador;
    protected Pais $paisColombia;
    protected Provincia $pichincha;
    protected Provincia $antioquia;
    protected Ciudad $quito;
    protected Course $curso;
    protected Leccion $leccion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paisEcuador = Pais::create(['nombre' => 'Ecuador', 'codigo' => 'EC']);
        $this->paisColombia = Pais::create(['nombre' => 'Colombia', 'codigo' => 'CO']);

        $this->pichincha = Provincia::create(['pais_id' => $this->paisEcuador->id, 'nombre' => 'Pichincha']);
        $this->antioquia = Provincia::create(['pais_id' => $this->paisColombia->id, 'nombre' => 'Antioquia']);

        $this->quito = Ciudad::create(['provincia_id' => $this->pichincha->id, 'nombre' => 'Quito']);

        $this->admin = User::factory()->create(['role' => 'admin']);

        $this->curso = Course::create([
            'user_id' => $this->admin->id,
            'title' => 'Armonía Tonal',
            'description' => 'Curso de armonía clásica',
            'level' => 'Principiante',
            'status' => 'Publicado',
        ]);

        $this->leccion = Leccion::create([
            'course_id' => $this->curso->id,
            'titulo' => 'Tríadas Mayores',
            'contenido' => 'Teoría de acordes mayores',
            'habilidad' => 'Tríadas Mayores',
        ]);
    }

    /**
     * Prueba que el endpoint de provincias retorna únicamente las del país solicitado.
     */
    public function test_api_provincias_retorna_provincias_del_pais(): void
    {
        $response = $this->actingAs($this->admin)->getJson("/api/paises/{$this->paisEcuador->id}/provincias");

        $response->assertOk()
            ->assertJsonFragment(['nombre' => 'Pichincha'])
            ->assertJsonMissing(['nombre' => 'Antioquia']);
    }

    /**
     * Prueba que el endpoint de ciudades retorna las ciudades de la provincia.
     */
    public function test_api_ciudades_retorna_ciudades_de_la_provincia(): void
    {
        $response = $this->actingAs($this->admin)->getJson("/api/provincias/{$this->pichincha->id}/ciudades");

        $response->assertOk()
            ->assertJsonFragment(['nombre' => 'Quito']);
    }

    /**
     * Prueba que el endpoint de lecciones retorna las del curso solicitado.
     */
    public function test_api_lecciones_retorna_lecciones_del_curso(): void
    {
        $response = $this->actingAs($this->admin)->getJson("/api/cursos/{$this->curso->id}/lecciones");

        $response->assertOk()
            ->assertJsonFragment(['titulo' => 'Tríadas Mayores']);
    }

    /**
     * Prueba que el Back-End RECHAZA una provincia que no pertenece al país seleccionado.
     */
    public function test_rechaza_inconsistencia_entre_pais_y_provincia(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/usuarios', [
            'name' => 'Prueba Inconsistencia',
            'email' => 'inconsistente@udla.edu.ec',
            'password' => 'secret1234',
            'role' => 'estudiante',
            'cedula' => '1710034065',
            'pais_id' => $this->paisEcuador->id,
            'provincia_id' => $this->antioquia->id, // Antioquia pertenece a Colombia, no a Ecuador
            'ciudad_id' => $this->quito->id,
        ]);

        $response->assertSessionHasErrors(['provincia_id']);
    }
}
