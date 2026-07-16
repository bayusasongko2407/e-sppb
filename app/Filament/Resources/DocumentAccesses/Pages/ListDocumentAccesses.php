<?php

namespace App\Filament\Resources\DocumentAccesses\Pages;

use App\Filament\Resources\DocumentAccesses\DocumentAccessResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDocumentAccesses extends ListRecords
{
    protected static string $resource = DocumentAccessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
