<?php

declare(strict_types=1);

namespace App\Filament\Resources\MyApprovals\Pages;

use App\Filament\Resources\MyApprovals\MyApprovalResource;
use Filament\Resources\Pages\ViewRecord;

class ViewMyApproval extends ViewRecord
{
    protected static string $resource = MyApprovalResource::class;
}
