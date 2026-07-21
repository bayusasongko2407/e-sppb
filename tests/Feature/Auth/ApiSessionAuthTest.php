<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiSessionAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_via_api_session_using_email(): void
    {
        $user = User::factory()->create([
            'email' => 'sessionuser@esppb.local',
            'password' => bcrypt('secret123'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/session/login', [
            'email' => 'sessionuser@esppb.local',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Session login berhasil.',
            ])
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.email', 'sessionuser@esppb.local');

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_login_via_api_session_using_nik(): void
    {
        $user = User::factory()->create([
            'nik' => '1234567890123456',
            'password' => bcrypt('secret123'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/session/login', [
            'email' => '1234567890123456',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Session login berhasil.',
            ])
            ->assertJsonPath('data.user.id', $user->id);

        $this->assertAuthenticatedAs($user);
    }

    public function test_api_session_me_returns_profile(): void
    {
        $user = User::factory()->create([
            'email' => 'profileuser@esppb.local',
            'password' => bcrypt('secret123'),
            'is_active' => true,
        ]);

        $this->actingAs($user, 'web');

        $response = $this->getJson('/api/v1/auth/session/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('data.id', $user->id);
    }

    public function test_api_session_logout_invalidates_session(): void
    {
        $user = User::factory()->create([
            'email' => 'logoutuser@esppb.local',
            'password' => bcrypt('secret123'),
            'is_active' => true,
        ]);

        $this->actingAs($user, 'web');

        $response = $this->postJson('/api/v1/auth/session/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Session logout berhasil.',
            ]);

        $this->assertGuest();
    }

    public function test_get_session_login_returns_json_405_instruction(): void
    {
        $response = $this->getJson('/api/v1/auth/session/login');

        $response->assertStatus(405)
            ->assertJson([
                'success' => false,
                'message' => 'Method GET tidak didukung untuk session login. Silakan gunakan method POST dengan payload email/nik dan password.',
            ]);
    }
}
