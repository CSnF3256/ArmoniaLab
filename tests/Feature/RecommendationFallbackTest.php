<?php

namespace Tests\Feature;

use App\Contracts\RecomendacionServiceContract;
use App\Models\Course;
use App\Models\Ejercicio;
use App\Models\Intento;
use App\Models\Leccion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecommendationFallbackTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    private Leccion $lesson;

    protected function setUp(): void
    {
        parent::setUp();

        $teacher = User::factory()->create(['role' => 'docente']);
        $this->student = User::factory()->create(['role' => 'estudiante']);
        $course = Course::create([
            'user_id' => $teacher->id,
            'title' => 'Curso de práctica',
            'description' => 'Práctica de recomendaciones',
            'level' => 'Principiante',
            'status' => 'Publicado',
        ]);
        $this->lesson = Leccion::create([
            'course_id' => $course->id,
            'titulo' => 'Lección de práctica',
            'contenido' => 'Teoría musical',
            'habilidad' => 'Tríadas Mayores',
        ]);
    }

    public function test_estudiante_sin_intentos_recibe_ejercicios_generales_de_diagnostico(): void
    {
        $exercise = $this->createExercise('Tríadas Mayores');

        $diagnosis = app(RecomendacionServiceContract::class)->obtenerDiagnostico($this->student);

        $this->assertSame(0, $diagnosis['total_intentos']);
        $this->assertCount(1, $diagnosis['recomendaciones']);
        $this->assertSame($exercise->id, $diagnosis['recomendaciones'][0]['ejercicio']->id);
        $this->assertStringContainsString('Evaluación diagnóstica inicial', $diagnosis['recomendaciones'][0]['motivo']);
    }

    public function test_sin_coincidencias_personalizadas_usa_ejercicios_generales(): void
    {
        $generalExercise = $this->createExercise('Intervalos');
        $this->recordFailedAttempt($generalExercise, 'Escalas sin ejercicios');

        $diagnosis = app(RecomendacionServiceContract::class)->obtenerDiagnostico($this->student);

        $this->assertSame('Escalas sin ejercicios', $diagnosis['habilidades_criticas'][0]['nombre']);
        $this->assertCount(1, $diagnosis['recomendaciones']);
        $this->assertSame($generalExercise->id, $diagnosis['recomendaciones'][0]['ejercicio']->id);
        $this->assertStringContainsString('Recomendación general', $diagnosis['recomendaciones'][0]['motivo']);
    }

    public function test_habilidad_debil_con_coincidencias_prioriza_recomendaciones_personalizadas(): void
    {
        $matchingExercise = $this->createExercise('Tríadas Menores');
        $this->createExercise('Intervalos');
        $this->recordFailedAttempt($matchingExercise, 'Tríadas Menores');

        $diagnosis = app(RecomendacionServiceContract::class)->obtenerDiagnostico($this->student);

        $this->assertSame('Tríadas Menores', $diagnosis['habilidades_criticas'][0]['nombre']);
        $this->assertNotEmpty($diagnosis['recomendaciones']);
        foreach ($diagnosis['recomendaciones'] as $recommendation) {
            $this->assertSame('Tríadas Menores', $recommendation['ejercicio']->habilidad);
            $this->assertStringContainsString('Práctica personalizada', $recommendation['motivo']);
        }
    }

    private function createExercise(string $skill): Ejercicio
    {
        return Ejercicio::create([
            'leccion_id' => $this->lesson->id,
            'pregunta' => "Pregunta de {$skill}",
            'tipo' => 'opcion_multiple',
            'habilidad' => $skill,
            'dificultad' => 'Fácil',
            'datos_pregunta' => ['opciones' => ['Do', 'Re']],
            'respuesta_correcta' => 'Do',
            'puntos' => 10,
        ]);
    }

    private function recordFailedAttempt(Ejercicio $exercise, string $skill): void
    {
        Intento::create([
            'user_id' => $this->student->id,
            'ejercicio_id' => $exercise->id,
            'respuesta_usuario' => 'Incorrecta',
            'es_correcta' => false,
            'puntaje_obtenido' => 0,
            'habilidad' => $skill,
        ]);
    }
}
