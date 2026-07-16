<?php

namespace Tests\Feature;

use App\Enums\SppbStatus;
use App\Models\Department;
use App\Models\Plant;
use App\Models\SppbHeader;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_redirects_to_login_for_guests(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function test_access_sppb_header_edit_route_with_encrypted_id(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $plant = Plant::factory()->create();
        $department = Department::factory()->create();

        $sppb = SppbHeader::factory()->create([
            'plant_id' => $plant->id,
            'department_id' => $department->id,
            'requester_id' => $user->id,
            'status' => SppbStatus::DRAFT->value,
        ]);

        $routeKey = $sppb->getRouteKey();
        $url = "/sppb-headers/{$routeKey}/edit";

        $response = $this->actingAs($user)->get($url);

        // It should either be 200 OK (if they can access) or 403 Forbidden (if policy stops it), but NOT 404!
        $this->assertNotEquals(404, $response->status(), 'Got 404 Not Found for URL: '.$url);
    }
}
