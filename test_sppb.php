<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Enums\SppbStatus;
use App\Models\SppbHeader;
use Illuminate\Contracts\Console\Kernel;

$sppb = SppbHeader::create([
    'plant_id' => 1,
    'department_id' => 1,
    'requester_id' => 1,
    'origin_location_id' => 1,
    'destination_location_id' => 2,
    'needed_name' => 'Test',
    'request_date' => now(),
    'date_needed' => now()->addDays(2),
    'purpose' => 'Test',
    'is_urgent' => false,
    'status' => SppbStatus::DRAFT->value,
]);

echo 'Created SPPB: '.$sppb->document_number."\n";
