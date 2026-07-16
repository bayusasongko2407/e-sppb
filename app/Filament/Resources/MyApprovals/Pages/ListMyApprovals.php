<?php

declare(strict_types=1);

namespace App\Filament\Resources\MyApprovals\Pages;

use App\Filament\Resources\MyApprovals\MyApprovalResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListMyApprovals extends ListRecords
{
    protected static string $resource = MyApprovalResource::class;

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }
}
