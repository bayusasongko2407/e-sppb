<?php

declare(strict_types=1);

namespace App\Filament\Resources\Items\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ItemInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('code')
                    ->label('Kode'),
                TextEntry::make('name')
                    ->label('Nama'),
                TextEntry::make('specification')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('unit.name')
                    ->label('Unit'),
                TextEntry::make('item_category')
                    ->placeholder('-'),
                IconEntry::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
