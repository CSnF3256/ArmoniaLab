<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Ejercicio;
use App\Models\Leccion;
use App\Models\User;
use App\Services\RecomendacionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EjercicioAutocorrectionTest extends TestCase
{
    use RefreshDatabase;

    protected User $estudiante;
    protected Course $curso;
    protected Leccion $leccion;
    protected Ejercicio $ejercicio;

    protected function setUp(): void
    {
        parent::setUp();

        $docente = User::factory()->create(['role' => 'docente']);
        $this->estudiante = User::factory()->create(['role' => 'estudiante']);

        $this->curso = Course::create([
            'user_id' => $docente->id,
            'title' => 'Fundamentos',
            'description' => 'Curso de fundamentos',
            'level' => 'Principiante',
            'status' => 'Publicado',
        ]);

        $this->leccion = Leccion::create([
            'course_id' => $this->curso->id,
            'titulo' => 'Tríadas Mayores',
            'contenido' => 'Teoría de tríadas',
            'habilidad' => 'Tríadas Mayores',
        ]);

        $this->ejercicio = Ejercicio::create([
            'leccion_id' => $this->leccion->id,
            'pregunta' => '¿Cuáles son las notas de Do Mayor?',
            'tipo' => 'opcion_multiple',
            'habilidad' => 'Tríadas Mayores',
            'dificultad' => 'Fácil',
            'datos_pregunta' => ['opciones' => ['Do - Mi - Sol', 'Do - Fa - La']],
            'respuesta_correcta' => 'Do - Mi - Sol',
            'explicacion' => 'Do Mayor es Do, Mi y Sol.',
            'puntos' => 10,
        ]);
    }

    /**
     * Prueba que el Back-End evalúa automáticamente la respuesta correcta y registra el intento.
     */
    public function test_autocorreccion_aprueba_respuesta_correcta(): void
    {
        $response = $this->actingAs($this->estudiante)->post("/ejercicios/{$this->ejercicio->id}/responder", [
            'respuesta' => 'Do - Mi - Sol',
            'tiempo_segundos' => 15,
        ]);

        $response->assertRedirect(route('ejercicios.show', $this->ejercicio));
        $response->assertSessionHas('resultado_exito');

        $this->assertDatabaseHas('intentos', [
            'user_id' => $this->estudiante->id,
            'ejercicio_id' => $this->ejercicio->id,
            'respuesta_usuario' => 'Do - Mi - Sol',
            'es_correcta' => true,
            'puntaje_obtenido' => 10,
        ]);

        $this->assertDatabaseHas('progreso_lecciones', [
            'user_id' => $this->estudiante->id,
            'leccion_id' => $this->leccion->id,
            'estatus' => 'completada',
        ]);
    }

    /**
     * Prueba que el Back-End evalúa automáticamente una respuesta errónea y registra el intento fallido.
     */
    public function test_autocorreccion_reprueba_respuesta_incorrecta(): void
    {
        $response = $this->actingAs($this->estudiante)->post("/ejercicios/{$this->ejercicio->id}/responder", [
            'respuesta' => 'Do - Fa - La',
            'tiempo_segundos' => 10,
        ]);

        $response->assertRedirect(route('ejercicios.show', $this->ejercicio));
        $response->assertSessionHas('resultado_error');

        $this->assertDatabaseHas('intentos', [
            'user_id' => $this->estudiante->id,
            'ejercicio_id' => $this->ejercicio->id,
            'respuesta_usuario' => 'Do - Fa - La',
            'es_correcta' => false,
            'puntaje_obtenido' => 0,
        ]);
    }

    /**
     * Prueba que el servicio de recomendación identifica habilidades débiles y propone prácticas.
     */
    public function test_recomendacion_personalizada_detecta_debilidad(): void
    {
        // El estudiante falla en el ejercicio
        $this->actingAs($this->estudiante)->post("/ejercicios/{$this->ejercicio->id}/responder", [
            'respuesta' => 'Do - Fa - La',
        ]);

        $service = new RecomendacionService();
        $diagnostico = $service->obtenerDiagnostico($this->estudiante);

        $this->assertEquals(0, $diagnostico['porcentaje_global']);
        $this->assertCount(1, $diagnostico['habilidades_criticas']);
        $this->assertEquals('Tríadas Mayores', $diagnostico['habilidades_criticas'][0]['nombre']);
        $this->assertNotEmpty($diagnostico['recomendaciones']);
    }
}
