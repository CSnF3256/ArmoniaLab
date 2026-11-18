<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Ejercicio;
use App\Models\Leccion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * test(audio): cubrir permisos y errores de captura
 *
 * Pruebas de Feature para la funcionalidad de captura de micrófono.
 */
class AudioCapturaTest extends TestCase
{
    use RefreshDatabase;

    protected User $estudiante;

    protected User $admin;

    protected Course $curso;

    protected Leccion $leccion;

    protected function setUp(): void
    {
        parent::setUp();

        $docente = User::factory()->create(['role' => 'docente']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->estudiante = User::factory()->create(['role' => 'estudiante']);

        $this->curso = Course::create([
            'user_id' => $docente->id,
            'title' => 'Curso de Audio',
            'description' => 'Pruebas de captura de audio',
            'level' => 'Principiante',
            'status' => 'Publicado',
        ]);

        $this->leccion = Leccion::create([
            'course_id' => $this->curso->id,
            'titulo' => 'Lección con Audio',
            'contenido' => 'Teoría de captura',
            'habilidad' => 'Triadas Mayores',
        ]);
    }

    // ── Helpers ─────────────────────────────────────────────────────

    /**
     * Crea un ejercicio clásico con audio_captura habilitado en metadata.
     */
    private function crearEjercicioConAudio(array $overrides = []): Ejercicio
    {
        return Ejercicio::create(array_merge([
            'leccion_id' => $this->leccion->id,
            'pregunta' => '¿Qué acorde forman las notas Do, Mi, Sol?',
            'opcion_1' => 'Do Mayor',
            'opcion_2' => 'La Menor',
            'respuesta_correcta' => 'Do Mayor',
            'explicacion' => 'Do-Mi-Sol forman la tríada de Do Mayor.',
            'habilidad' => 'Triadas Mayores',
            'modalidad' => 'clasica',
            'metadata' => [
                'audio_captura' => true,
                'audio_notas_objetivo' => ['Do4', 'Mi4', 'Sol4'],
                'pista' => 'Escucha el acorde',
            ],
        ], $overrides));
    }

    /**
     * Crea un ejercicio clásico SIN audio (caso control).
     */
    private function crearEjercicioSinAudio(array $overrides = []): Ejercicio
    {
        return Ejercicio::create(array_merge([
            'leccion_id' => $this->leccion->id,
            'pregunta' => '¿Cuántos semitonos tiene una tercera mayor?',
            'opcion_1' => '4',
            'opcion_2' => '3',
            'respuesta_correcta' => '4',
            'explicacion' => 'Una tercera mayor tiene 4 semitonos.',
            'habilidad' => 'Intervalos',
            'modalidad' => 'clasica',
            'metadata' => null,
        ], $overrides));
    }

    // ── Test 1: La vista show renderiza correctamente con audio_captura ──

    public function test_ejercicio_con_audio_captura_renderiza_sin_error(): void
    {
        $ejercicio = $this->crearEjercicioConAudio();

        $response = $this->actingAs($this->estudiante)
            ->get(route('ejercicios.show', $ejercicio));

        $response->assertStatus(200);
        $response->assertViewIs('ejercicios.show');
        $response->assertViewHas('ejercicio', fn ($e) => $e->id === $ejercicio->id);
    }

    // ── Test 2: El panel de audio está presente cuando audio_captura = true ──

    public function test_vista_incluye_panel_audio_cuando_metadata_activa(): void
    {
        $ejercicio = $this->crearEjercicioConAudio();

        $response = $this->actingAs($this->estudiante)
            ->get(route('ejercicios.show', $ejercicio));

        $response->assertStatus(200);
        $response->assertSee('audio-captura-panel', false);
        $response->assertSee('audio-start-btn', false);
        $response->assertSee('audio-visualizer', false);
        $response->assertSee('getUserMedia', false);
        $response->assertSee('audio-recognition-panel', false);
        $response->assertSee('Do4 · Mi4 · Sol4', false);
        $response->assertSee('analyser.fftSize = 32768', false);
        $response->assertSee('audio-calibrate-btn', false);
        $response->assertSee('audio-confidence', false);
        $response->assertSee('detectarNotaAjena', false);
        $response->assertSee('getFloatTimeDomainData', false);
    }

    // ── Test 3: Ejercicio SIN audio NO incluye el panel de micrófono ────

    public function test_vista_no_incluye_panel_audio_sin_metadata(): void
    {
        $ejercicio = $this->crearEjercicioSinAudio();

        $response = $this->actingAs($this->estudiante)
            ->get(route('ejercicios.show', $ejercicio));

        $response->assertStatus(200);
        $response->assertDontSee('audio-captura-panel', false);
        $response->assertDontSee('getUserMedia', false);
    }

    // ── Test 4: El endpoint responder() funciona con tiempo_segundos ────

    public function test_responder_acepta_tiempo_segundos_como_en_ejercicios_con_audio(): void
    {
        $ejercicio = $this->crearEjercicioConAudio();

        $response = $this->actingAs($this->estudiante)
            ->post(route('ejercicios.responder', $ejercicio), [
                'respuesta' => 'Do Mayor',
                'tiempo_segundos' => 15,
            ]);

        $this->assertNotEquals(500, $response->status());
        $this->assertNotEquals(422, $response->status());
    }

    // ── Test 5: Respuesta correcta con audio activo ─────────────────────

    public function test_responder_respuesta_correcta_con_audio_captura(): void
    {
        $ejercicio = $this->crearEjercicioConAudio([
            'respuesta_correcta' => 'Do Mayor',
        ]);

        $response = $this->actingAs($this->estudiante)
            ->post(route('ejercicios.responder', $ejercicio), [
                'respuesta' => 'Do Mayor',
                'tiempo_segundos' => 8,
            ]);

        $response->assertRedirect(route('ejercicios.show', $ejercicio));
        $response->assertSessionHas('resultado_exito');
    }

    // ── Test 6: Respuesta incorrecta con audio activo ───────────────────

    public function test_responder_respuesta_incorrecta_con_audio_captura(): void
    {
        $ejercicio = $this->crearEjercicioConAudio([
            'respuesta_correcta' => 'Do Mayor',
        ]);

        $response = $this->actingAs($this->estudiante)
            ->post(route('ejercicios.responder', $ejercicio), [
                'respuesta' => 'La Menor',
                'tiempo_segundos' => 20,
            ]);

        $response->assertRedirect(route('ejercicios.show', $ejercicio));
        $response->assertSessionHas('resultado_error');
    }

    // ── Test 7: La metadata audio_captura persiste en base de datos ─────

    public function test_metadata_audio_captura_persiste_correctamente(): void
    {
        $ejercicio = $this->crearEjercicioConAudio();

        $this->assertNotNull($ejercicio->metadata);
        $this->assertTrue((bool) ($ejercicio->metadata['audio_captura'] ?? false));
        $this->assertSame('Escucha el acorde', $ejercicio->metadata['pista'] ?? null);
    }

    // ── Test 8: Admin puede crear un ejercicio con audio_captura en metadata ─

    public function test_admin_puede_crear_ejercicio_con_metadata_audio_captura(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.ejercicios.store'), [
                'course_id' => $this->curso->id,
                'leccion_id' => $this->leccion->id,
                'pregunta' => 'Ejercicio con captura de audio',
                'tipo' => 'opcion_multiple',
                'habilidad' => 'Triadas',
                'dificultad' => 'Fácil',
                'modalidad' => 'clasica',
                'opcion_1' => 'Do Mayor',
                'opcion_2' => 'Sol Mayor',
                'opcion_3' => 'La Menor',
                'opcion_4' => 'Fa Mayor',
                'respuesta_correcta' => 'Do Mayor',
                'explicacion' => 'El acorde correcto.',
                'puntos' => 10,
                'metadata_pista' => 'Escucha atentamente',
                'audio_captura' => true,
                'audio_notas_objetivo' => 'Do4, Mi4, Sol4',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        $ejercicio = Ejercicio::latest('id')->firstOrFail();
        $this->assertTrue($ejercicio->metadata['audio_captura']);
        $this->assertSame(['Do4', 'Mi4', 'Sol4'], $ejercicio->metadata['audio_notas_objetivo']);
    }

    public function test_admin_rechaza_notas_sin_octava_para_reconocimiento(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.ejercicios.store'), [
                'course_id' => $this->curso->id,
                'leccion_id' => $this->leccion->id,
                'pregunta' => 'Toca el acorde indicado',
                'tipo' => 'opcion_multiple',
                'habilidad' => 'Triadas',
                'dificultad' => 'Fácil',
                'modalidad' => 'clasica',
                'opcion_1' => 'Do Mayor',
                'opcion_2' => 'Sol Mayor',
                'respuesta_correcta' => 'Do Mayor',
                'puntos' => 10,
                'audio_captura' => true,
                'audio_notas_objetivo' => 'Do, Mi, Sol',
            ]);

        $response->assertSessionHasErrors(['audio_notas_objetivo']);
        $this->assertDatabaseCount('ejercicios', 0);
    }

    // ── Test 9: Regresión — modalidades clásicas no se ven afectadas ────

    public function test_modalidades_clasicas_no_afectadas_por_cambios_audio(): void
    {
        $ejercicio = $this->crearEjercicioSinAudio([
            'modalidad' => 'verdadero_falso',
            'pregunta' => '¿La escala mayor tiene 7 notas?',
            'opcion_1' => 'Verdadero',
            'opcion_2' => 'Falso',
            'respuesta_correcta' => 'Verdadero',
        ]);

        $response = $this->actingAs($this->estudiante)
            ->post(route('ejercicios.responder', $ejercicio), [
                'respuesta' => 'Verdadero',
            ]);

        $response->assertSessionHas('resultado_exito');
    }

    // ── Test 10: Usuario no autenticado no puede acceder a ejercicios ────

    public function test_usuario_no_autenticado_redirigido_al_intentar_ver_ejercicio_con_audio(): void
    {
        $ejercicio = $this->crearEjercicioConAudio();

        $response = $this->get(route('ejercicios.show', $ejercicio));

        $response->assertRedirect(route('login'));
    }

    // ── Test 11: Respuesta vacía rechazada (sin importar si hay audio activo) ─

    public function test_responder_rechaza_respuesta_vacia(): void
    {
        $ejercicio = $this->crearEjercicioConAudio();

        $response = $this->actingAs($this->estudiante)
            ->post(route('ejercicios.responder', $ejercicio), [
                'respuesta' => '',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['respuesta']);
    }

    // ── Test 12: metadata con audio_captura = false no muestra el panel ──

    public function test_ejercicio_con_audio_captura_false_no_muestra_panel(): void
    {
        $ejercicio = $this->crearEjercicioSinAudio([
            'metadata' => ['audio_captura' => false, 'pista' => 'Sin audio'],
        ]);

        $response = $this->actingAs($this->estudiante)
            ->get(route('ejercicios.show', $ejercicio));

        $response->assertStatus(200);
        $response->assertDontSee('audio-captura-panel', false);
    }
}
