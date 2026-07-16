<?php

namespace App\Filament\Resources\RunningNumbers\Pages;

use App\Filament\Resources\RunningNumbers\RunningNumberResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRunningNumber extends CreateRecord
{
    protected static string $resource = RunningNumberResource::class;
}
