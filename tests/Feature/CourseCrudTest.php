<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_a_course(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/cursos', [
            'title' => 'Teoría musical básica',
            'description' => 'Notas, intervalos y construcción de acordes.',
            'level' => 'Principiante',
            'status' => 'Borrador',
        ])->assertRedirect('/cursos');

        $this->assertDatabaseHas('courses', ['user_id' => $user->id, 'title' => 'Teoría musical básica']);
    }

    public function test_user_cannot_edit_another_users_course(): void
    {
        $owner = User::factory()->create();
        $visitor = User::factory()->create();
        $course = $owner->courses()->create([
            'title' => 'Curso privado',
            'description' => 'Contenido del propietario.', 'level' => 'Intermedio', 'status' => 'Borrador',
        ]);

        $this->actingAs($visitor)->get(route('cursos.edit', $course))->assertForbidden();
    }

    public function test_owner_can_update_and_delete_a_course(): void
    {
        $user = User::factory()->create();
        $course = $user->courses()->create([
            'title' => 'Nombre anterior',
            'description' => 'Descripción anterior.', 'level' => 'Principiante', 'status' => 'Borrador',
        ]);

        $this->actingAs($user)->put(route('cursos.update', $course), [
            'title' => 'Nombre actualizado', 'description' => 'Descripción actualizada.',
            'level' => 'Intermedio', 'status' => 'Publicado',
        ])->assertRedirect('/cursos');

        $this->assertDatabaseHas('courses', ['title' => 'Nombre actualizado']);
        $this->actingAs($user)->delete(route('cursos.destroy', $course))->assertRedirect('/cursos');
        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }
}
