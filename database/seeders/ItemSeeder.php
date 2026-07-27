<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = Unit::all()->pluck('id', 'code');

        $items = [
            // Spare Part
            [
                'code' => 'SP-MC-001',
                'name' => 'Centrifugal Pump Seal 2 Inch',
                'specification' => 'Silicon Carbide/Viton Seal for Water Pump',
                'unit_id' => $units->get('PCS'),
                'item_category' => 'SPARE_PART',
                'is_active' => true,
            ],
            [
                'code' => 'SP-MC-002',
                'name' => 'Gearbox Reducer NMRV 050 1:30',
                'specification' => 'Ratio 1:30, Input Flange 0.75HP',
                'unit_id' => $units->get('PCS'),
                'item_category' => 'SPARE_PART',
                'is_active' => true,
            ],
            [
                'code' => 'SP-MC-003',
                'name' => 'Heating Element Packaging Machine 300W',
                'specification' => 'Length 200mm, 220V, Stainless Steel',
                'unit_id' => $units->get('PCS'),
                'item_category' => 'SPARE_PART',
                'is_active' => true,
            ],
            [
                'code' => 'SP-EL-001',
                'name' => 'Limit Switch Omron WLCA2-2',
                'specification' => 'Roller lever type, IP67',
                'unit_id' => $units->get('PCS'),
                'item_category' => 'SPARE_PART',
                'is_active' => true,
            ],
            [
                'code' => 'SP-EL-002',
                'name' => 'MCB 3 Phase Schneider 16A',
                'specification' => 'Domae Series, 4.5kA',
                'unit_id' => $units->get('PCS'),
                'item_category' => 'SPARE_PART',
                'is_active' => true,
            ],

            // Consumable
            [
                'code' => 'CS-SL-001',
                'name' => 'WD-40 Multi-Use Product 400ml',
                'specification' => 'Penetrant and Lubricant Spray',
                'unit_id' => $units->get('CAN') ?? $units->get('PCS'),
                'item_category' => 'CONSUMABLE',
                'is_active' => true,
            ],
            [
                'code' => 'CS-SL-002',
                'name' => 'Teflon Tape 1/2 Inch',
                'specification' => 'Thread seal tape for piping, Length 10m',
                'unit_id' => $units->get('ROLL') ?? $units->get('PCS'),
                'item_category' => 'CONSUMABLE',
                'is_active' => true,
            ],
            [
                'code' => 'CS-SL-003',
                'name' => 'Cable Tie 200mm Black',
                'specification' => 'Nylon 66, Pack of 100 pcs',
                'unit_id' => $units->get('PACK') ?? $units->get('PCS'),
                'item_category' => 'CONSUMABLE',
                'is_active' => true,
            ],

            // Material
            [
                'code' => 'RM-CP-001',
                'name' => 'Green Coffee Beans Robusta Dampit',
                'specification' => 'Grade 1, Moisture max 12.5%',
                'unit_id' => $units->get('SAK') ?? $units->get('PCS'),
                'item_category' => 'MATERIAL',
                'is_active' => true,
            ],
            [
                'code' => 'RM-CP-002',
                'name' => 'Green Coffee Beans Arabica Gayo',
                'specification' => 'Grade 1, Moisture max 12.5%',
                'unit_id' => $units->get('SAK') ?? $units->get('PCS'),
                'item_category' => 'MATERIAL',
                'is_active' => true,
            ],
            [
                'code' => 'RM-PK-001',
                'name' => 'Roll Foil Kopi Kapal Api Mix 165g',
                'specification' => 'Aluminium Foil Roll, Width 220mm',
                'unit_id' => $units->get('ROLL') ?? $units->get('PCS'),
                'item_category' => 'MATERIAL',
                'is_active' => true,
            ],

            // Equipment
            [
                'code' => 'EQ-TL-001',
                'name' => 'Digital Vernier Caliper Mitutoyo 150mm',
                'specification' => 'Accuracy 0.02mm, Stainless Steel',
                'unit_id' => $units->get('PCS'),
                'item_category' => 'EQUIPMENT',
                'is_active' => true,
            ],
            [
                'code' => 'EQ-TL-002',
                'name' => 'Digital Multimeter Fluke 117',
                'specification' => 'True RMS, AC/DC Voltage & Current',
                'unit_id' => $units->get('PCS'),
                'item_category' => 'EQUIPMENT',
                'is_active' => true,
            ],
        ];

        foreach ($items as $item) {
            Item::firstOrCreate(['code' => $item['code']], $item);
        }
    }
}
