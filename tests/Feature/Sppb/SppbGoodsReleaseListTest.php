<?php

declare(strict_types=1);

namespace Tests\Feature\Sppb;

use App\Filament\Resources\SppbHeaders\Schemas\SppbHeaderForm;
use App\Models\Department;
use App\Models\GoodsRelease;
use App\Models\Plant;
use App\Models\SppbHeader;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SppbGoodsReleaseListTest extends TestCase
{
    use RefreshDatabase;

    public function test_render_goods_release_list_returns_placeholder_when_no_surat_jalan_exists(): void
    {
        $plant = Plant::factory()->create();
        $dept = Department::factory()->create(['plant_id' => $plant->id]);
        $user = User::factory()->create(['plant_id' => $plant->id, 'department_id' => $dept->id]);

        $sppb = SppbHeader::factory()->create([
            'plant_id' => $plant->id,
            'department_id' => $dept->id,
            'requester_id' => $user->id,
            'status' => 'APPROVED',
        ]);

        $html = SppbHeaderForm::renderGoodsReleaseList($sppb)->toHtml();

        $this->assertStringContainsString('Belum ada Surat Jalan yang dibuat untuk dokumen SPPB ini.', $html);
    }

    public function test_render_goods_release_list_renders_related_surat_jalan_details(): void
    {
        $plant = Plant::factory()->create();
        $dept = Department::factory()->create(['plant_id' => $plant->id]);
        $user = User::factory()->create(['name' => 'Ahmad Pengirim', 'plant_id' => $plant->id, 'department_id' => $dept->id]);

        $sppb = SppbHeader::factory()->create([
            'plant_id' => $plant->id,
            'department_id' => $dept->id,
            'requester_id' => $user->id,
            'status' => 'RELEASE_IN_PROGRESS',
        ]);

        $receiver = User::factory()->create(['name' => 'Siti Penerima', 'plant_id' => $plant->id, 'department_id' => $dept->id]);

        $release = GoodsRelease::factory()->create([
            'sppb_header_id' => $sppb->id,
            'release_number' => 'SJ-20260729-0099',
            'status' => 'RELEASED',
            'delivery_date' => now()->toDateString(),
            'created_by_id' => $user->id,
            'recipient_name' => 'Siti Penerima',
            'driver_name' => 'Budi Pengemudi',
            'vehicle_number' => 'B 1234 CD',
            'expedition_name' => 'JNE Express',
        ]);

        $html = SppbHeaderForm::renderGoodsReleaseList($sppb)->toHtml();

        $this->assertStringContainsString('Penerima', $html);
        $this->assertStringContainsString('SJ-20260729-0099', $html);
        $this->assertStringContainsString('Dikirim', $html);
        $this->assertStringContainsString('Ahmad Pengirim', $html);
        $this->assertStringContainsString('Siti Penerima', $html);
        $this->assertStringContainsString('Budi Pengemudi', $html);
        $this->assertStringContainsString('B 1234 CD', $html);
        $this->assertStringContainsString('JNE Express', $html);
    }
}
