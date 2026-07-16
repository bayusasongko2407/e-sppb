<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Filament\Pages\Auth\CustomLogin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LoginFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_successfully(): void
    {
        $this->get('/login')
            ->assertStatus(200);
    }

    public function test_user_can_login_using_email(): void
    {
        $user = User::factory()->create([
            'email' => 'user@esppb.local',
            'password' => bcrypt('secret123'),
            'is_active' => true,
        ]);

        Livewire::test(CustomLogin::class)
            ->fillForm([
                'email' => 'user@esppb.local',
                'password' => 'secret123',
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_login_using_nik(): void
    {
        $user = User::factory()->create([
            'nik' => '9876543210123456',
            'password' => bcrypt('secret123'),
            'is_active' => true,
        ]);

        Livewire::test(CustomLogin::class)
            ->fillForm([
                'email' => '9876543210123456',
                'password' => 'secret123',
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticatedAs($user);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive@esppb.local',
            'password' => bcrypt('secret123'),
            'is_active' => false,
        ]);

        Livewire::test(CustomLogin::class)
            ->fillForm([
                'email' => 'inactive@esppb.local',
                'password' => 'secret123',
            ])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $this->assertGuest();
    }
}
