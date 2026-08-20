<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\GoodsReleaseStatus;
use App\Models\EnumControl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoodsReleaseStatusIndonesianTest extends TestCase
{
    use RefreshDatabase;

    public function test_goods_release_status_returns_indonesian_labels(): void
    {
        $this->assertEquals('Draft', GoodsReleaseStatus::DRAFT->label());
        $this->assertEquals('Dalam Pengiriman', GoodsReleaseStatus::RELEASED->label());
        $this->assertEquals('Dalam Perjalanan', GoodsReleaseStatus::IN_TRANSIT->label());
        $this->assertEquals('Sudah Diterima', GoodsReleaseStatus::DELIVERED->label());
        $this->assertEquals('Diterima', GoodsReleaseStatus::RECEIVED->label());
        $this->assertEquals('Dibatalkan', GoodsReleaseStatus::CANCELLED->label());
    }

    public function test_goods_release_status_label_can_be_overridden_by_enum_control(): void
    {
        EnumControl::create([
            'table_name' => 'goods_releases',
            'column_name' => 'status',
            'value' => 'RELEASED',
            'label' => 'Sedang Dikirimkan Oleh Kurir',
            'sequence' => 10,
            'is_active' => true,
        ]);

        $this->assertEquals('Sedang Dikirimkan Oleh Kurir', GoodsReleaseStatus::RELEASED->label());
    }
}
