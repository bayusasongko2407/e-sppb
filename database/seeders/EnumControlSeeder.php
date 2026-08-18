<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\EnumControl;
use Illuminate\Database\Seeder;

class EnumControlSeeder extends Seeder
{
    public function run(): void
    {
        $enums = [
            // SPPB Header Statuses
            ['table_name' => 'sppb_headers', 'column_name' => 'status', 'value' => 'DRAFT', 'label' => 'Draft', 'sequence' => 10],
            ['table_name' => 'sppb_headers', 'column_name' => 'status', 'value' => 'SUBMISSION_QUEUED', 'label' => 'Sedang Diproses', 'sequence' => 15],
            ['table_name' => 'sppb_headers', 'column_name' => 'status', 'value' => 'WAITING_APPROVAL', 'label' => 'Menunggu Persetujuan', 'sequence' => 20],
            ['table_name' => 'sppb_headers', 'column_name' => 'status', 'value' => 'WAITING_APPROVAL_MANAGER', 'label' => 'Menunggu Persetujuan Manager', 'sequence' => 25],
            ['table_name' => 'sppb_headers', 'column_name' => 'status', 'value' => 'WAITING_VERIFICATION_BAT', 'label' => 'Menunggu Verifikasi BAT', 'sequence' => 30],
            ['table_name' => 'sppb_headers', 'column_name' => 'status', 'value' => 'PROCESS_VERIFICATION_BAT', 'label' => 'Proses Verifikasi BAT', 'sequence' => 35],
            ['table_name' => 'sppb_headers', 'column_name' => 'status', 'value' => 'APPROVED', 'label' => 'Disetujui', 'sequence' => 40],
            ['table_name' => 'sppb_headers', 'column_name' => 'status', 'value' => 'REVISION_REQUIRED', 'label' => 'Perlu Revisi', 'sequence' => 45],
            ['table_name' => 'sppb_headers', 'column_name' => 'status', 'value' => 'RELEASE_IN_PROGRESS', 'label' => 'Proses Pengeluaran Barang', 'sequence' => 50],
            ['table_name' => 'sppb_headers', 'column_name' => 'status', 'value' => 'COMPLETED', 'label' => 'Selesai', 'sequence' => 60],
            ['table_name' => 'sppb_headers', 'column_name' => 'status', 'value' => 'REJECTED', 'label' => 'Ditolak', 'sequence' => 70],
            ['table_name' => 'sppb_headers', 'column_name' => 'status', 'value' => 'CANCELLED', 'label' => 'Dibatalkan', 'sequence' => 80],

            // Goods Release Statuses (Surat Jalan)
            ['table_name' => 'goods_releases', 'column_name' => 'status', 'value' => 'DRAFT', 'label' => 'Draft', 'sequence' => 10],
            ['table_name' => 'goods_releases', 'column_name' => 'status', 'value' => 'RELEASED', 'label' => 'Diterbitkan / Dikirim', 'sequence' => 20],
            ['table_name' => 'goods_releases', 'column_name' => 'status', 'value' => 'IN_TRANSIT', 'label' => 'Dalam Perjalanan', 'sequence' => 30],
            ['table_name' => 'goods_releases', 'column_name' => 'status', 'value' => 'DELIVERED', 'label' => 'Sudah Diterima (e-POD)', 'sequence' => 40],
            ['table_name' => 'goods_releases', 'column_name' => 'status', 'value' => 'RECEIVED', 'label' => 'Diterima', 'sequence' => 50],
            ['table_name' => 'goods_releases', 'column_name' => 'status', 'value' => 'CANCELLED', 'label' => 'Dibatalkan', 'sequence' => 60],

            // SPPB Details Delivery Statuses
            ['table_name' => 'sppb_details', 'column_name' => 'delivery_status', 'value' => 'PENDING', 'label' => 'Belum Dikirim', 'sequence' => 10],
            ['table_name' => 'sppb_details', 'column_name' => 'delivery_status', 'value' => 'PARTIALLY_RELEASED', 'label' => 'Rilis Sebagian', 'sequence' => 20],
            ['table_name' => 'sppb_details', 'column_name' => 'delivery_status', 'value' => 'FULLY_RELEASED', 'label' => 'Rilis Penuh', 'sequence' => 30],
            ['table_name' => 'sppb_details', 'column_name' => 'delivery_status', 'value' => 'PARTIALLY_DELIVERED', 'label' => 'Pengiriman Sebagian', 'sequence' => 40],
            ['table_name' => 'sppb_details', 'column_name' => 'delivery_status', 'value' => 'DELIVERED', 'label' => 'Pengiriman Penuh / Diterima', 'sequence' => 50],

            // Asset conditions
            ['table_name' => 'assets', 'column_name' => 'condition', 'value' => 'GOOD', 'label' => 'Baik', 'sequence' => 10],
            ['table_name' => 'assets', 'column_name' => 'condition', 'value' => 'NEEDS_REPAIR', 'label' => 'Perlu Perbaikan', 'sequence' => 20],
            ['table_name' => 'assets', 'column_name' => 'condition', 'value' => 'BROKEN', 'label' => 'Rusak', 'sequence' => 30],
            ['table_name' => 'assets', 'column_name' => 'condition', 'value' => 'SCRAP', 'label' => 'Scrap / Afkir', 'sequence' => 40],

            // Asset status
            ['table_name' => 'assets', 'column_name' => 'status', 'value' => 'AVAILABLE', 'label' => 'Tersedia', 'sequence' => 10],
            ['table_name' => 'assets', 'column_name' => 'status', 'value' => 'IN_USE', 'label' => 'Digunakan', 'sequence' => 20],
            ['table_name' => 'assets', 'column_name' => 'status', 'value' => 'CLASS_A', 'label' => 'Kelas A', 'sequence' => 30],
            ['table_name' => 'assets', 'column_name' => 'status', 'value' => 'CLASS_B', 'label' => 'Kelas B', 'sequence' => 40],

            // Item category
            ['table_name' => 'items', 'column_name' => 'item_category', 'value' => 'CONSUMABLE', 'label' => 'Barang Habis Pakai (Consumable)', 'sequence' => 10],
            ['table_name' => 'items', 'column_name' => 'item_category', 'value' => 'SPARE_PART', 'label' => 'Suku Cadang', 'sequence' => 20],
            ['table_name' => 'items', 'column_name' => 'item_category', 'value' => 'MATERIAL', 'label' => 'Bahan Baku', 'sequence' => 30],
            ['table_name' => 'items', 'column_name' => 'item_category', 'value' => 'EQUIPMENT', 'label' => 'Peralatan', 'sequence' => 40],

            // Unit category
            ['table_name' => 'units', 'column_name' => 'category', 'value' => 'BERAT', 'label' => 'Berat', 'sequence' => 10],
            ['table_name' => 'units', 'column_name' => 'category', 'value' => 'VOLUME', 'label' => 'Volume', 'sequence' => 20],
            ['table_name' => 'units', 'column_name' => 'category', 'value' => 'PANJANG', 'label' => 'Panjang', 'sequence' => 30],
            ['table_name' => 'units', 'column_name' => 'category', 'value' => 'LUAS', 'label' => 'Luas', 'sequence' => 40],
            ['table_name' => 'units', 'column_name' => 'category', 'value' => 'HITUNGAN', 'label' => 'Hitungan / Qty', 'sequence' => 50],
            ['table_name' => 'units', 'column_name' => 'category', 'value' => 'KEMASAN', 'label' => 'Kemasan', 'sequence' => 60],
            ['table_name' => 'units', 'column_name' => 'category', 'value' => 'LAINNYA', 'label' => 'Lainnya', 'sequence' => 70],
        ];

        foreach ($enums as $enum) {
            EnumControl::firstOrCreate([
                'table_name' => $enum['table_name'],
                'column_name' => $enum['column_name'],
                'value' => $enum['value'],
            ], [
                'label' => $enum['label'],
                'sequence' => $enum['sequence'],
                'is_active' => true,
            ]);
        }
    }
}
