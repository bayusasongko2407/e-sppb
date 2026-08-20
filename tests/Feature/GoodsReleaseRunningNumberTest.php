<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Department;
use App\Models\GoodsRelease;
use App\Models\Plant;
use App\Models\RunningNumber;
use App\Models\SppbHeader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoodsReleaseRunningNumberTest extends TestCase
{
    use RefreshDatabase;

    public function test_goods_release_generates_number_using_active_running_number_config(): void
    {
        $plant = Plant::factory()->create(['code' => 'P01']);
        $department = Department::factory()->create(['plant_id' => $plant->id, 'code' => 'LOG']);

        $sppb = SppbHeader::factory()->create([
            'plant_id' => $plant->id,
            'department_id' => $department->id,
        ]);

        RunningNumber::firstOrCreate([
            'plant_id' => $plant->id,
            'department_id' => $department->id,
            'document_type' => 'GR',
            'period_key' => date('Y-m'),
        ], [
            'prefix' => '{DEP}-SJ/{YY}{MM}/',
            'digits' => 4,
            'last_number' => 0,
            'is_active' => true,
        ]);

        $release = GoodsRelease::factory()->create([
            'sppb_header_id' => $sppb->id,
            'release_number' => null,
            'driver_name' => 'Budi',
        ]);

        $expectedNumber = 'LOG-SJ/'.date('ym').'/0001';
        $this->assertEquals($expectedNumber, $release->release_number);
    }
}
