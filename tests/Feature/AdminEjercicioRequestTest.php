<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Leccion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEjercicioRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Course $course;

    private Leccion $lesson;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->course = Course::create([
            'user_id' => $this->admin->id,
            'title' => 'Curso de solicitudes',
            'description' => 'Curso para validar Form Requests.',
            'level' => 'Principiante',
            'status' => 'Publicado',
        ]);
        $this->lesson = Leccion::create([
            'course_id' => $this->course->id,
            'titulo' => 'Tríadas',
            'contenido' => 'Contenido de prueba',
            'habilidad' => 'Tríadas',
        ]);
    }

    public function test_requiere_notas_objetivo_cuando_audio_esta_activo(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.ejercicios.store'), $this->validPayload([
                'audio_captura' => true,
                'audio_notas_objetivo' => '',
            ]));

        $response->assertSessionHasErrors('audio_notas_objetivo');
        $this->assertDatabaseCount('ejercicios', 0);
    }

    public function test_rechaza_notas_enarmonicas_duplicadas_en_la_misma_octava(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.ejercicios.store'), $this->validPayload([
                'audio_captura' => true,
                'audio_notas_objetivo' => 'Do#4, Reb4, Sol4',
            ]));

        $response->assertSessionHasErrors('audio_notas_objetivo');
        $this->assertDatabaseCount('ejercicios', 0);
    }

    /** @param array<string, mixed> $overrides */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'course_id' => $this->course->id,
            'leccion_id' => $this->lesson->id,
            'pregunta' => '¿Qué acorde escuchas?',
            'tipo' => 'opcion_multiple',
            'modalidad' => 'clasica',
            'habilidad' => 'Tríadas',
            'dificultad' => 'Fácil',
            'opcion_1' => 'Mayor',
            'opcion_2' => 'Menor',
            'respuesta_correcta' => 'Mayor',
            'puntos' => 10,
        ], $overrides);
    }
}
