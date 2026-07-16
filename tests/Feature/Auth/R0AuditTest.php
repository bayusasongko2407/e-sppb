<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\Attachment;
use App\Models\User;
use App\Services\Auth\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class R0AuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_factory_generates_valid_model()
    {
        $user = User::factory()->create();
        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertTrue(Hash::check('password', $user->password)); // Ensure password works in factory
    }

    public function test_attachment_relation_to_uploaded_by_user()
    {
        $user = User::factory()->create();
        $attachment = Attachment::factory()->create(['uploader_id' => $user->id]);

        $this->assertEquals($user->id, $attachment->uploader->id);
        $this->assertInstanceOf(User::class, $attachment->uploader);
    }

    public function test_login_with_email()
    {
        $user = User::factory()->create(['email' => 'test@local.dev', 'password' => bcrypt('secret123')]);

        $authService = app(AuthService::class);
        $loggedInUser = $authService->attemptLogin('test@local.dev', 'secret123');

        $this->assertEquals($user->id, $loggedInUser->id);
    }

    public function test_login_with_nik()
    {
        $user = User::factory()->create(['nik' => '1234567890', 'password' => bcrypt('secret123')]);

        $authService = app(AuthService::class);
        $loggedInUser = $authService->attemptLogin('1234567890', 'secret123');

        $this->assertEquals($user->id, $loggedInUser->id);
    }

    public function test_login_with_invalid_credentials_throws_exception()
    {
        $user = User::factory()->create(['email' => 'test@local.dev', 'password' => bcrypt('secret123')]);

        $authService = app(AuthService::class);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Kredensial yang diberikan tidak cocok dengan catatan kami.');

        $authService->attemptLogin('test@local.dev', 'wrongpassword');
    }

    public function test_login_with_inactive_user_throws_exception()
    {
        $user = User::factory()->create([
            'email' => 'test@local.dev',
            'password' => bcrypt('secret123'),
            'is_active' => false,
        ]);

        $authService = app(AuthService::class);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Akun Anda tidak aktif. Silakan hubungi administrator.');

        $authService->attemptLogin('test@local.dev', 'secret123');
    }

    public function test_failed_login_threshold_locks_user()
    {
        $user = User::factory()->create([
            'email' => 'test@local.dev',
            'password' => bcrypt('secret123'),
            'failed_login_attempts' => 4,
        ]);

        $authService = app(AuthService::class);

        // 5th attempt should lock the user
        try {
            $authService->attemptLogin('test@local.dev', 'wrongpassword');
        } catch (ValidationException $e) {
        }

        $user->refresh();
        $this->assertEquals(5, $user->failed_login_attempts);
        $this->assertNotNull($user->locked_until);
        $this->assertTrue($user->locked_until->isFuture());

        // 6th attempt should throw locked exception
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches('/Akun Anda terkunci/');
        $authService->attemptLogin('test@local.dev', 'secret123');
    }
}
