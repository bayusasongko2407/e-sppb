<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Filament\Pages\MyProfile;
use App\Filament\Resources\EmailChangeRequests\Pages\ListEmailChangeRequests;
use App\Models\EmailChangeRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfileFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.env' => 'local']);

        $this->artisan('auth:sync-permissions');

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    }

    public function test_user_can_access_profile_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(MyProfile::getUrl());

        $response->assertStatus(200);
    }

    public function test_user_can_change_name_but_email_creates_pending_request(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@esppb.local',
        ]);

        $this->actingAs($user);

        Livewire::test(MyProfile::class)
            ->fillForm([
                'name' => 'New Name',
                'email' => 'new@esppb.local',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        // Refresh and check name updated, but email NOT updated
        $user->refresh();
        $this->assertEquals('New Name', $user->name);
        $this->assertEquals('old@esppb.local', $user->email);

        // Verify pending request exists
        $request = EmailChangeRequest::where('user_id', $user->id)->first();
        $this->assertNotNull($request);
        $this->assertEquals('old@esppb.local', $request->old_email);
        $this->assertEquals('new@esppb.local', $request->new_email);
        $this->assertEquals('PENDING', $request->status);
    }

    public function test_password_requires_strong_validation(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('OldPassword123!'),
        ]);

        $this->actingAs($user);

        // Try weak password
        Livewire::test(MyProfile::class)
            ->fillForm([
                'name' => $user->name,
                'email' => $user->email,
                'password' => 'weak',
                'password_confirmation' => 'weak',
            ])
            ->call('save')
            ->assertHasFormErrors(['password']);

        // Try same password as current
        Livewire::test(MyProfile::class)
            ->fillForm([
                'name' => $user->name,
                'email' => $user->email,
                'password' => 'OldPassword123!',
                'password_confirmation' => 'OldPassword123!',
            ])
            ->call('save')
            ->assertHasFormErrors(['password']);

        // Try valid strong password
        Livewire::test(MyProfile::class)
            ->fillForm([
                'name' => $user->name,
                'email' => $user->email,
                'password' => 'NewPassword999#',
                'password_confirmation' => 'NewPassword999#',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword999#', $user->password));
    }

    public function test_super_admin_can_approve_email_change_request(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $user = User::factory()->create([
            'email' => 'old@esppb.local',
        ]);

        $request = EmailChangeRequest::create([
            'user_id' => $user->id,
            'old_email' => 'old@esppb.local',
            'new_email' => 'new@esppb.local',
            'status' => 'PENDING',
        ]);

        $this->actingAs($superAdmin);

        Livewire::test(ListEmailChangeRequests::class)
            ->callTableAction('approve', $request)
            ->assertHasNoTableActionErrors();

        $request->refresh();
        $this->assertEquals('APPROVED', $request->status);
        $this->assertEquals($superAdmin->id, $request->approved_by_id);

        $user->refresh();
        $this->assertEquals('new@esppb.local', $user->email);
    }

    public function test_super_admin_can_reject_email_change_request(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $user = User::factory()->create([
            'email' => 'old@esppb.local',
        ]);

        $request = EmailChangeRequest::create([
            'user_id' => $user->id,
            'old_email' => 'old@esppb.local',
            'new_email' => 'new@esppb.local',
            'status' => 'PENDING',
        ]);

        $this->actingAs($superAdmin);

        Livewire::test(ListEmailChangeRequests::class)
            ->callTableAction('reject', $request, [
                'reason' => 'Invalid domain',
            ])
            ->assertHasNoTableActionErrors();

        $request->refresh();
        $this->assertEquals('REJECTED', $request->status);
        $this->assertEquals('Invalid domain', $request->reason);

        $user->refresh();
        $this->assertEquals('old@esppb.local', $user->email);
    }
}
