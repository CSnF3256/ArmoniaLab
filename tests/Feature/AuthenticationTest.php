<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
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

    public function test_login_is_temporarily_blocked_after_five_failed_attempts(): void
    {
        $user = User::factory()->create([
            'email' => 'seguridad@armonialab.test',
            'password' => 'password',
        ]);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post('/login', [
                'email' => $user->email,
                'password' => 'incorrecta',
            ])->assertSessionHasErrors('email');
        }

        $blockedResponse = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $blockedResponse->assertSessionHasErrors('email');
        $this->assertStringContainsString(
            'Demasiados intentos',
            $blockedResponse->getSession()->get('errors')->first('email')
        );

        $this->assertGuest();

        RateLimiter::clear(Str::lower($user->email).'|127.0.0.1');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect('/cursos');

        $this->assertAuthenticatedAs($user);
    }
}
