<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_administration(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_student_can_view_courses_but_cannot_create_them(): void
    {
        $student = User::factory()->create(['role' => 'estudiante']);

        $this->actingAs($student)->get(route('cursos.index'))->assertOk();
        $this->actingAs($student)->get(route('cursos.create'))->assertForbidden();
        $this->actingAs($student)->post(route('cursos.store'), [
            'title' => 'Curso no autorizado',
            'description' => 'Este curso no debe guardarse.',
            'level' => 'Principiante',
            'status' => 'Borrador',
        ])->assertForbidden();

        $this->assertDatabaseMissing('courses', ['title' => 'Curso no autorizado']);
    }

    public function test_only_student_can_access_personal_progress(): void
    {
        $student = User::factory()->create(['role' => 'estudiante']);
        $teacher = User::factory()->create(['role' => 'docente']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($student)->get(route('progreso.index'))->assertOk();
        $this->actingAs($teacher)->get(route('progreso.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('progreso.index'))->assertForbidden();
    }

    public function test_teacher_manages_content_but_not_users(): void
    {
        $teacher = User::factory()->create(['role' => 'docente']);

        $this->actingAs($teacher)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($teacher)->get(route('admin.ejercicios.index'))->assertOk();
        $this->actingAs($teacher)->get(route('admin.tipos-acordes.index'))->assertOk();
        $this->actingAs($teacher)->get(route('admin.usuarios.index'))->assertForbidden();
        $this->actingAs($teacher)->post(route('admin.usuarios.store'), [])->assertForbidden();
    }

    public function test_student_cannot_access_or_modify_administration(): void
    {
        $student = User::factory()->create(['role' => 'estudiante']);

        $this->actingAs($student)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($student)->get(route('admin.ejercicios.index'))->assertForbidden();
        $this->actingAs($student)->get(route('admin.tipos-acordes.index'))->assertForbidden();
        $this->actingAs($student)->get(route('admin.usuarios.index'))->assertForbidden();
        $this->actingAs($student)->post(route('admin.ejercicios.store'), [])->assertForbidden();
        $this->actingAs($student)->post(route('admin.tipos-acordes.store'), [])->assertForbidden();

        $this->assertDatabaseCount('ejercicios', 0);
        $this->assertDatabaseCount('tipo_acordes', 0);
    }

    public function test_admin_can_access_user_management(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('admin.usuarios.index'))->assertOk();
    }

    public function test_admin_can_manage_any_course(): void
    {
        $teacher = User::factory()->create(['role' => 'docente']);
        $admin = User::factory()->create(['role' => 'admin']);
        $course = $teacher->courses()->create([
            'title' => 'Curso del docente',
            'description' => 'Contenido administrable por un administrador.',
            'level' => 'Intermedio',
            'status' => 'Publicado',
        ]);

        $this->actingAs($admin)->get(route('cursos.edit', $course))->assertOk();
    }

    public function test_public_registration_always_creates_a_student(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Cuenta Pública',
            'email' => 'publica@example.test',
            'role' => 'admin',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('acordes.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'publica@example.test',
            'role' => 'estudiante',
        ]);
    }
}
