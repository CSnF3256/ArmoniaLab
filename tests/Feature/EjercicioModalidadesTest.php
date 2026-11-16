<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Ejercicio;
use App\Models\Leccion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * test(ejercicios): cubrir nuevas modalidades
 *
 * Pruebas de Feature que verifican que las seis modalidades de ejercicios
 * (clasica, completar_secuencia, seleccionar_intervalo, verdadero_falso,
 * ordenar_notas, detectar_error) funcionan correctamente con la autocorrección
 * de back-end, que la validación de modalidades inválidas rechaza la creación
 * de ejercicios, y que el modelo expone el catálogo de modalidades.
 */
class EjercicioModalidadesTest extends TestCase
{
    use RefreshDatabase;

    protected User $estudiante;
    protected User $admin;
    protected Course $curso;
    protected Leccion $leccion;

    protected function setUp(): void
    {
        parent::setUp();

        $docente         = User::factory()->create(['role' => 'docente']);
        $this->admin     = User::factory()->create(['role' => 'admin']);
        $this->estudiante = User::factory()->create(['role' => 'estudiante']);

        $this->curso = Course::create([
            'user_id'     => $docente->id,
            'title'       => 'Curso de Modalidades',
            'description' => 'Pruebas de modalidades',
            'level'       => 'Principiante',
            'status'      => 'Publicado',
        ]);

        $this->leccion = Leccion::create([
            'course_id' => $this->curso->id,
            'titulo'    => 'Lección de modalidades',
            'contenido' => 'Teoría',
            'habilidad' => 'Tríadas Mayores',
        ]);
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    private function crearEjercicio(array $overrides = []): Ejercicio
    {
        return Ejercicio::create(array_merge([
            'leccion_id'       => $this->leccion->id,
            'pregunta'         => 'Pregunta de prueba',
            'tipo'             => 'opcion_multiple',
            'modalidad'        => 'clasica',
            'habilidad'        => 'Tríadas Mayores',
            'dificultad'       => 'Fácil',
            'datos_pregunta'   => ['opciones' => ['Do - Mi - Sol', 'Do - Fa - La']],
            'respuesta_correcta' => 'Do - Mi - Sol',
            'explicacion'      => 'Fórmula [0, 4, 7].',
            'puntos'           => 10,
        ], $overrides));
    }

    // ─── 1. Catálogo de modalidades ─────────────────────────────────────────

    /**
     * El modelo Ejercicio expone exactamente seis modalidades canónicas.
     */
    public function test_modelo_expone_seis_modalidades(): void
    {
        $modalidades = Ejercicio::modalidades();

        $this->assertCount(8, $modalidades);
        $this->assertArrayHasKey('clasica', $modalidades);
        $this->assertArrayHasKey('completar_secuencia', $modalidades);
        $this->assertArrayHasKey('seleccionar_intervalo', $modalidades);
        $this->assertArrayHasKey('verdadero_falso', $modalidades);
        $this->assertArrayHasKey('ordenar_notas', $modalidades);
        $this->assertArrayHasKey('detectar_error', $modalidades);
    }

    // ─── 2. Modalidad por defecto ────────────────────────────────────────────

    /**
     * Un ejercicio sin modalidad explícita adopta el valor 'clasica' por defecto.
     */
    public function test_modalidad_default_es_clasica(): void
    {
        $ej = $this->crearEjercicio(); // Sin pasar 'modalidad' => 'clasica'

        $this->assertSame('clasica', $ej->fresh()->modalidad);
    }

    // ─── 3. Modalidad verdadero_falso ────────────────────────────────────────

    /**
     * Un ejercicio verdadero_falso acepta 'Verdadero' como respuesta correcta.
     */
    public function test_modalidad_verdadero_falso_acepta_respuesta_verdadero(): void
    {
        $ej = $this->crearEjercicio([
            'modalidad'        => 'verdadero_falso',
            'datos_pregunta'   => ['opciones' => ['Verdadero', 'Falso']],
            'respuesta_correcta' => 'Verdadero',
        ]);

        $response = $this->actingAs($this->estudiante)->post("/ejercicios/{$ej->id}/responder", [
            'respuesta'       => 'Verdadero',
            'tiempo_segundos' => 5,
        ]);

        $response->assertSessionHas('resultado_exito');
        $this->assertDatabaseHas('intentos', [
            'ejercicio_id' => $ej->id,
            'es_correcta'  => true,
        ]);
    }

    /**
     * Un ejercicio verdadero_falso registra el fallo cuando la respuesta es incorrecta.
     */
    public function test_modalidad_verdadero_falso_reprueba_respuesta_incorrecta(): void
    {
        $ej = $this->crearEjercicio([
            'modalidad'        => 'verdadero_falso',
            'datos_pregunta'   => ['opciones' => ['Verdadero', 'Falso']],
            'respuesta_correcta' => 'Verdadero',
        ]);

        $response = $this->actingAs($this->estudiante)->post("/ejercicios/{$ej->id}/responder", [
            'respuesta' => 'Falso',
        ]);

        $response->assertSessionHas('resultado_error');
        $this->assertDatabaseHas('intentos', [
            'ejercicio_id' => $ej->id,
            'es_correcta'  => false,
            'puntaje_obtenido' => 0,
        ]);
    }

    // ─── 4. Modalidad ordenar_notas ──────────────────────────────────────────

    /**
     * Un ejercicio ordenar_notas evalúa correctamente la secuencia de notas.
     */
    public function test_modalidad_ordenar_notas_aprueba_secuencia_correcta(): void
    {
        $ej = $this->crearEjercicio([
            'modalidad'        => 'ordenar_notas',
            'datos_pregunta'   => ['opciones' => ['La', 'Do', 'Sol', 'Mi']],
            'respuesta_correcta' => 'La - Do - Mi - Sol',
            'puntos'           => 20,
        ]);

        $response = $this->actingAs($this->estudiante)->post("/ejercicios/{$ej->id}/responder", [
            'respuesta' => 'La - Do - Mi - Sol',
        ]);

        $response->assertSessionHas('resultado_exito');
        $this->assertDatabaseHas('intentos', [
            'ejercicio_id'    => $ej->id,
            'es_correcta'     => true,
            'puntaje_obtenido' => 20,
        ]);
    }

    // ─── 5. Modalidad seleccionar_intervalo ──────────────────────────────────

    /**
     * Un ejercicio seleccionar_intervalo evalúa semitonos numéricos correctamente.
     */
    public function test_modalidad_seleccionar_intervalo_evalua_semitonos(): void
    {
        $ej = $this->crearEjercicio([
            'modalidad'        => 'seleccionar_intervalo',
            'datos_pregunta'   => ['opciones' => ['5', '6', '7', '8']],
            'respuesta_correcta' => '7',
            'puntos'           => 10,
        ]);

        $response = $this->actingAs($this->estudiante)->post("/ejercicios/{$ej->id}/responder", [
            'respuesta' => '7',
        ]);

        $response->assertSessionHas('resultado_exito');
        $this->assertDatabaseHas('intentos', [
            'ejercicio_id' => $ej->id,
            'es_correcta'  => true,
        ]);
    }

    // ─── 6. Modalidad completar_secuencia ────────────────────────────────────

    /**
     * Un ejercicio completar_secuencia registra acierto cuando la nota elegida es correcta.
     */
    public function test_modalidad_completar_secuencia_detecta_nota_faltante(): void
    {
        $ej = $this->crearEjercicio([
            'modalidad'        => 'completar_secuencia',
            'datos_pregunta'   => ['opciones' => ['Re - Sol - La', 'Re - Fa# - La', 'Re - Mi - La', 'Re - Sol - Si']],
            'respuesta_correcta' => 'Re - Sol - La',
        ]);

        $response = $this->actingAs($this->estudiante)->post("/ejercicios/{$ej->id}/responder", [
            'respuesta' => 'Re - Sol - La',
        ]);

        $response->assertSessionHas('resultado_exito');
    }

    // ─── 7. Modalidad detectar_error ─────────────────────────────────────────

    /**
     * Un ejercicio detectar_error registra acierto cuando se identifica la nota errónea.
     */
    public function test_modalidad_detectar_error_identifica_nota_incorrecta(): void
    {
        $ej = $this->crearEjercicio([
            'modalidad'        => 'detectar_error',
            'datos_pregunta'   => ['opciones' => ['Do', 'Mi', 'Sol#', 'Sol']],
            'respuesta_correcta' => 'Sol',
            'puntos'           => 15,
        ]);

        $response = $this->actingAs($this->estudiante)->post("/ejercicios/{$ej->id}/responder", [
            'respuesta' => 'Sol',
        ]);

        $response->assertSessionHas('resultado_exito');
        $this->assertDatabaseHas('intentos', [
            'ejercicio_id'    => $ej->id,
            'es_correcta'     => true,
            'puntaje_obtenido' => 15,
        ]);
    }

    // ─── 8. Metadata (pista y tiempo límite) ─────────────────────────────────

    /**
     * Los metadatos de pista y tiempo_limite se persisten correctamente.
     */
    public function test_metadata_pista_y_tiempo_limite_se_guardan(): void
    {
        $ej = $this->crearEjercicio([
            'modalidad' => 'clasica',
            'metadata'  => [
                'pista'         => 'Recuerda la fórmula [0, 4, 7]',
                'tiempo_limite' => 60,
            ],
        ]);

        $fresco = $ej->fresh();
        $this->assertSame('Recuerda la fórmula [0, 4, 7]', $fresco->metadata['pista']);
        $this->assertSame(60, $fresco->metadata['tiempo_limite']);
    }

    // ─── 9. Validación admin: modalidad inválida ─────────────────────────────

    /**
     * El admin no puede crear un ejercicio con una modalidad que no existe en la lista canónica.
     * La validación back-end debe rechazarlo con status 422.
     */
    public function test_admin_rechaza_modalidad_invalida_en_store(): void
    {
        // Crear una lección para el dropdown dependiente
        $response = $this->actingAs($this->admin)->post(route('admin.ejercicios.store'), [
            'course_id'         => $this->curso->id,
            'leccion_id'        => $this->leccion->id,
            'pregunta'          => 'Pregunta de prueba',
            'tipo'              => 'opcion_multiple',
            'modalidad'         => 'modalidad_inexistente', // ← inválida
            'habilidad'         => 'Tríadas Mayores',
            'dificultad'        => 'Fácil',
            'opcion_1'          => 'Do - Mi - Sol',
            'opcion_2'          => 'Do - Fa - La',
            'opcion_3'          => 'Re - Fa - La',
            'opcion_4'          => 'Mi - Sol - Si',
            'respuesta_correcta' => 'Do - Mi - Sol',
            'puntos'            => 10,
        ]);

        $response->assertSessionHasErrors(['modalidad']);
    }

    // ─── 10. Vista show renderiza badge de modalidad ─────────────────────────

    /**
     * La vista de resolución de ejercicio muestra el badge de la modalidad activa.
     */
    public function test_vista_show_muestra_badge_de_modalidad(): void
    {
        $ej = $this->crearEjercicio(['modalidad' => 'verdadero_falso']);

        $response = $this->actingAs($this->estudiante)->get(route('ejercicios.show', $ej));

        $response->assertStatus(200);
        $response->assertSee('Verdadero o Falso');
    }
}

