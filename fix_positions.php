<?php

use App\Models\Position;
use App\Models\User;
use App\Models\UserPosition;

// Bersihkan mapping sebelumnya (jika ada) selain Super Admin
UserPosition::where('user_id', '!=', 1)->delete();

$spvPos = Position::where('code', 'SPV')->first();
$batPos = Position::where('code', 'BAT')->first();
$mgrPos = Position::where('code', 'MANAGER')->first();

$spvUser = User::where('email', 'supervisor@esppb.local')->first();
$batUser = User::where('email', 'bat@esppb.local')->first();
$mgrUser = User::where('email', 'manager@esppb.local')->first();

if ($spvUser && $spvPos) {
    UserPosition::firstOrCreate([
        'user_id' => $spvUser->id,
        'position_id' => $spvPos->id,
    ], [
        'is_primary' => true,
        'is_active' => true,
    ]);
    echo "Mapped Supervisor to SPV\n";
}

if ($batUser && $batPos) {
    UserPosition::firstOrCreate([
        'user_id' => $batUser->id,
        'position_id' => $batPos->id,
    ], [
        'is_primary' => true,
        'is_active' => true,
    ]);
    echo "Mapped BAT to BAT\n";
}

if ($mgrUser && $mgrPos) {
    UserPosition::firstOrCreate([
        'user_id' => $mgrUser->id,
        'position_id' => $mgrPos->id,
    ], [
        'is_primary' => true,
        'is_active' => true,
    ]);
    echo "Mapped Manager to MANAGER\n";
}

echo "Perbaikan Selesai.\n";
