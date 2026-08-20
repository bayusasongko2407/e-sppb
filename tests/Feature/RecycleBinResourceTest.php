<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\RecycleBins\Pages\ListRecycleBins;
use App\Filament\Resources\RecycleBins\RecycleBinResource;
use App\Models\Department;
use App\Models\GoodsRelease;
use App\Models\Plant;
use App\Models\SppbHeader;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RecycleBinResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Plant $plant;

    protected Department $department;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'requester', 'guard_name' => 'web']);

        $this->user = User::factory()->create();
        $this->user->assignRole('super_admin');

        $this->plant = Plant::factory()->create();
        $this->department = Department::factory()->create(['plant_id' => $this->plant->id]);
    }

    public function test_can_render_recycle_bin_page(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ListRecycleBins::class)
            ->assertSuccessful();
    }

    public function test_displays_only_trashed_sppb_headers(): void
    {
        $activeHeader = SppbHeader::factory()->create([
            'plant_id' => $this->plant->id,
            'department_id' => $this->department->id,
            'requester_id' => $this->user->id,
        ]);

        $trashedHeader = SppbHeader::factory()->create([
            'plant_id' => $this->plant->id,
            'department_id' => $this->department->id,
            'requester_id' => $this->user->id,
        ]);
        $trashedHeader->delete();

        $this->actingAs($this->user);

        Livewire::test(ListRecycleBins::class)
            ->assertCanSeeTableRecords([$trashedHeader])
            ->assertCanNotSeeTableRecords([$activeHeader]);
    }

    public function test_can_restore_trashed_sppb_header(): void
    {
        $trashedHeader = SppbHeader::factory()->create([
            'plant_id' => $this->plant->id,
            'department_id' => $this->department->id,
            'requester_id' => $this->user->id,
        ]);
        $trashedHeader->delete();

        $this->assertTrue($trashedHeader->trashed());

        $this->actingAs($this->user);

        Livewire::test(ListRecycleBins::class)
            ->callTableAction('restore', $trashedHeader);

        $this->assertFalse($trashedHeader->fresh()->trashed());
    }

    public function test_can_force_delete_trashed_sppb_header(): void
    {
        $trashedHeader = SppbHeader::factory()->create([
            'plant_id' => $this->plant->id,
            'department_id' => $this->department->id,
            'requester_id' => $this->user->id,
        ]);
        $trashedHeader->delete();

        $this->actingAs($this->user);

        Livewire::test(ListRecycleBins::class)
            ->callTableAction('forceDelete', $trashedHeader);

        $this->assertDatabaseMissing('sppb_headers', [
            'id' => $trashedHeader->id,
        ]);
    }

    public function test_cannot_soft_delete_sppb_if_active_goods_release_exists(): void
    {
        $header = SppbHeader::factory()->create([
            'plant_id' => $this->plant->id,
            'department_id' => $this->department->id,
            'requester_id' => $this->user->id,
        ]);

        $goodsRelease = GoodsRelease::create([
            'uuid' => (string) Str::uuid(),
            'release_number' => 'SJ-TEST-001',
            'sppb_header_id' => $header->id,
            'release_sequence' => 1,
            'is_manual' => false,
            'created_by_id' => $this->user->id,
            'sender_name' => 'Pengirim Test',
            'receiver_name' => 'Penerima Test',
            'status' => 'DRAFT',
            'verification_hash' => hash('sha256', 'SJ-TEST-001'),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Dokumen SPPB ini tidak dapat dihapus karena masih memiliki Surat Jalan aktif');

        $header->delete();
    }

    public function test_can_soft_delete_sppb_after_goods_release_is_deleted(): void
    {
        $header = SppbHeader::factory()->create([
            'plant_id' => $this->plant->id,
            'department_id' => $this->department->id,
            'requester_id' => $this->user->id,
        ]);

        $goodsRelease = GoodsRelease::create([
            'uuid' => (string) Str::uuid(),
            'release_number' => 'SJ-TEST-002',
            'sppb_header_id' => $header->id,
            'release_sequence' => 1,
            'is_manual' => false,
            'created_by_id' => $this->user->id,
            'sender_name' => 'Pengirim Test',
            'receiver_name' => 'Penerima Test',
            'status' => 'DRAFT',
            'verification_hash' => hash('sha256', 'SJ-TEST-002'),
        ]);

        // Hapus dulu Surat Jalan (soft delete)
        $goodsRelease->delete();
        $this->assertTrue($goodsRelease->trashed());

        // Sekarang SPPB bisa dihapus (soft delete)
        $header->delete();
        $this->assertTrue($header->trashed());
    }

    public function test_non_superadmin_cannot_access_trashed_records(): void
    {
        $normalUser = User::factory()->create();
        $normalUser->assignRole('requester');

        $trashedHeader = SppbHeader::factory()->create([
            'plant_id' => $this->plant->id,
            'department_id' => $this->department->id,
            'requester_id' => $normalUser->id,
        ]);
        $trashedHeader->delete();

        $this->actingAs($normalUser);

        $this->assertFalse($normalUser->can('view', $trashedHeader));
    }

    public function test_non_superadmin_cannot_access_recycle_bin_resource_page(): void
    {
        $normalUser = User::factory()->create();
        $normalUser->assignRole('requester');

        $this->actingAs($normalUser);

        $this->assertFalse(RecycleBinResource::canViewAny());
    }
}
