<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Ejercicio;
use App\Models\Leccion;
use App\Models\TipoAcorde;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCoreCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_puede_editar_y_actualizar_tipo_de_acorde(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tipo = TipoAcorde::create([
            'nombre' => 'Acorde Temporal',
            'simbolo' => 'tmp',
            'categoria' => 'Tríadas',
            'intervalos_semitonos' => '[0, 4, 7]',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.tipos-acordes.edit', $tipo))
            ->assertOk()
            ->assertSee('Editar Tipo de Acorde');

        $response = $this->actingAs($admin)
            ->put(route('admin.tipos-acordes.update', $tipo), [
                'nombre' => 'Novena Musical',
                'simbolo' => 'add9',
                'categoria' => 'Extendidos',
                'intervalos_semitonos' => '0, 4, 7, 14',
                'descripcion' => 'Actualizado desde el CRUD administrativo.',
            ]);

        $response->assertRedirect(route('admin.tipos-acordes.index'));
        $this->assertDatabaseHas('tipo_acordes', [
            'id' => $tipo->id,
            'nombre' => 'Novena Musical',
            'intervalos_semitonos' => '[0, 4, 7, 14]',
        ]);
    }

    public function test_admin_puede_editar_y_actualizar_ejercicio(): void
    {
        [$admin, $curso, $leccion, $tipo, $ejercicio] = $this->crearDatosDeEjercicio();

        $this->actingAs($admin)
            ->get(route('admin.ejercicios.edit', $ejercicio))
            ->assertOk()
            ->assertSee('Editar Ejercicio Interactivo');

        $response = $this->actingAs($admin)
            ->put(route('admin.ejercicios.update', $ejercicio), [
                'course_id' => $curso->id,
                'leccion_id' => $leccion->id,
                'tipo_acorde_id' => $tipo->id,
                'pregunta' => 'Pregunta actualizada',
                'tipo' => 'opcion_multiple',
                'habilidad' => 'Tríadas Actualizadas',
                'dificultad' => 'Medio',
                'opcion_1' => 'Do - Mi - Sol',
                'opcion_2' => 'Do - Mib - Sol',
                'opcion_3' => 'Re - Fa - La',
                'opcion_4' => 'Mi - Sol - Si',
                'respuesta_correcta' => 'Do - Mi - Sol',
                'explicacion' => 'Retroalimentación actualizada.',
                'puntos' => 25,
            ]);

        $response->assertRedirect(route('admin.ejercicios.index'));
        $this->assertDatabaseHas('ejercicios', [
            'id' => $ejercicio->id,
            'pregunta' => 'Pregunta actualizada',
            'puntos' => 25,
        ]);
    }

    public function test_actualizacion_rechaza_leccion_que_no_pertenece_al_curso(): void
    {
        [$admin, $curso, , $tipo, $ejercicio] = $this->crearDatosDeEjercicio();
        $otroCurso = Course::create([
            'user_id' => $admin->id,
            'title' => 'Otro curso',
            'description' => 'Curso separado',
            'level' => 'Intermedio',
            'status' => 'Publicado',
        ]);
        $otraLeccion = Leccion::create([
            'course_id' => $otroCurso->id,
            'titulo' => 'Otra lección',
            'contenido' => 'Contenido',
            'habilidad' => 'Otra habilidad',
        ]);

        $response = $this->actingAs($admin)
            ->put(route('admin.ejercicios.update', $ejercicio), [
                'course_id' => $curso->id,
                'leccion_id' => $otraLeccion->id,
                'tipo_acorde_id' => $tipo->id,
                'pregunta' => 'No debe guardarse',
                'tipo' => 'opcion_multiple',
                'habilidad' => 'Prueba',
                'dificultad' => 'Fácil',
                'opcion_1' => 'A',
                'opcion_2' => 'B',
                'opcion_3' => 'C',
                'opcion_4' => 'D',
                'respuesta_correcta' => 'A',
                'puntos' => 10,
            ]);

        $response->assertSessionHasErrors('leccion_id');
        $this->assertDatabaseMissing('ejercicios', [
            'id' => $ejercicio->id,
            'pregunta' => 'No debe guardarse',
        ]);
    }

    private function crearDatosDeEjercicio(): array
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $curso = Course::create([
            'user_id' => $admin->id,
            'title' => 'Armonía básica',
            'description' => 'Curso para pruebas',
            'level' => 'Principiante',
            'status' => 'Publicado',
        ]);
        $leccion = Leccion::create([
            'course_id' => $curso->id,
            'titulo' => 'Tríadas',
            'contenido' => 'Contenido de tríadas',
            'habilidad' => 'Tríadas',
        ]);
        $tipo = TipoAcorde::create([
            'nombre' => 'Mayor de prueba',
            'simbolo' => 'M',
            'categoria' => 'Tríadas',
            'intervalos_semitonos' => '[0, 4, 7]',
        ]);
        $ejercicio = Ejercicio::create([
            'leccion_id' => $leccion->id,
            'tipo_acorde_id' => $tipo->id,
            'pregunta' => 'Pregunta original',
            'tipo' => 'opcion_multiple',
            'habilidad' => 'Tríadas',
            'dificultad' => 'Fácil',
            'datos_pregunta' => ['opciones' => ['A', 'B', 'C', 'D']],
            'respuesta_correcta' => 'A',
            'puntos' => 10,
        ]);

        return [$admin, $curso, $leccion, $tipo, $ejercicio];
    }
}
