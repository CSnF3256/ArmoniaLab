<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SensitiveDataExposureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_serialization_hides_credentials_and_cedula(): void
    {
        $user = User::factory()->create([
            'cedula' => '1710034065',
            'password' => 'password',
            'remember_token' => 'token-privado',
        ]);

        $serialized = $user->toArray();

        $this->assertArrayNotHasKey('password', $serialized);
        $this->assertArrayNotHasKey('remember_token', $serialized);
        $this->assertArrayNotHasKey('cedula', $serialized);
        $this->assertSame($user->email, $serialized['email']);
    }

    public function test_authorized_admin_can_still_read_cedula_from_model(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'cedula' => '1710034065',
        ]);

        $this->assertSame('1710034065', $admin->cedula);
    }
}
