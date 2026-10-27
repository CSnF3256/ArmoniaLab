<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_protected_courses(): void
    {
        $this->get('/cursos')->assertRedirect('/login');
    }

    public function test_user_can_log_in_and_access_courses(): void
    {
        $user = User::factory()->create(['password' => 'password']);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/cursos');

        $this->assertAuthenticatedAs($user);
        $this->get('/cursos')->assertOk();
    }

    public function test_user_can_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }
}
