<?php

declare(strict_types=1);

namespace Tests\Feature\Reports;

use App\DTOs\Reporting\ReportScope;
use App\Models\Department;
use App\Models\GoodsRelease;
use App\Models\Plant;
use App\Models\SppbDetail;
use App\Models\SppbHeader;
use App\Models\User;
use App\Reports\SppbItemReport;
use App\Reports\SppbReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SppbReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_sppb_report_displays_creator_of_goods_release_as_sender_name(): void
    {
        $plant = Plant::factory()->create();
        $dept = Department::factory()->create(['plant_id' => $plant->id]);

        $requester = User::factory()->create(['plant_id' => $plant->id, 'department_id' => $dept->id]);
        $creatorOfSuratJalan = User::factory()->create([
            'name' => 'Budi Surat Jalan',
            'plant_id' => $plant->id,
            'department_id' => $dept->id,
        ]);

        $sppb = SppbHeader::factory()->create([
            'plant_id' => $plant->id,
            'department_id' => $dept->id,
            'requester_id' => $requester->id,
            'status' => 'APPROVED',
            'request_date' => now()->format('Y-m-d'),
        ]);

        $goodsRelease = GoodsRelease::factory()->create([
            'sppb_header_id' => $sppb->id,
            'created_by_id' => $creatorOfSuratJalan->id,
            'sender_name' => 'Gudang Utama Plant A',
        ]);

        $report = new SppbReport;
        $scope = new ReportScope(
            allowedModules: ['sppb', 'sppb_items'],
            allowedPlants: [$plant->id],
            allowedDepartments: [$dept->id],
            canPreview: true,
            canExport: true,
            canPrint: true,
        );

        $query = $report->getQuery($scope, []);
        $result = $query->first();

        $columns = $report->getTableColumns();
        $senderColumn = collect($columns)->firstWhere(fn ($col) => $col->getName() === 'sender_name');

        $this->assertNotNull($senderColumn);
        $stateUsing = (new \ReflectionProperty($senderColumn, 'getStateUsing'))->getValue($senderColumn);

        $this->assertEquals('Budi Surat Jalan', $stateUsing($result));
    }

    public function test_sppb_item_report_displays_creator_of_goods_release_as_sender_name(): void
    {
        $plant = Plant::factory()->create();
        $dept = Department::factory()->create(['plant_id' => $plant->id]);

        $requester = User::factory()->create(['plant_id' => $plant->id, 'department_id' => $dept->id]);
        $creatorOfSuratJalan = User::factory()->create([
            'name' => 'Siti Pembuat SJ',
            'plant_id' => $plant->id,
            'department_id' => $dept->id,
        ]);

        $sppb = SppbHeader::factory()->create([
            'plant_id' => $plant->id,
            'department_id' => $dept->id,
            'requester_id' => $requester->id,
            'status' => 'APPROVED',
            'request_date' => now()->format('Y-m-d'),
        ]);

        $detail = SppbDetail::factory()->create([
            'sppb_header_id' => $sppb->id,
        ]);

        GoodsRelease::factory()->create([
            'sppb_header_id' => $sppb->id,
            'created_by_id' => $creatorOfSuratJalan->id,
            'sender_name' => 'Gudang Utama Plant B',
        ]);

        $report = new SppbItemReport;
        $scope = new ReportScope(
            allowedModules: ['sppb', 'sppb_items'],
            allowedPlants: [$plant->id],
            allowedDepartments: [$dept->id],
            canPreview: true,
            canExport: true,
            canPrint: true,
        );

        $query = $report->getQuery($scope, []);
        $result = $query->first();

        $columns = $report->getTableColumns();
        $senderColumn = collect($columns)->firstWhere(fn ($col) => $col->getName() === 'sender_name');

        $this->assertNotNull($senderColumn);
        $stateUsing = (new \ReflectionProperty($senderColumn, 'getStateUsing'))->getValue($senderColumn);

        $this->assertEquals('Siti Pembuat SJ', $stateUsing($result));
    }
}
