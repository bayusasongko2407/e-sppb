<?php

declare(strict_types=1);

namespace App\Filament\Resources\MyApprovals;

use App\Filament\Resources\MyApprovals\Pages\ListMyApprovals;
use App\Filament\Resources\MyApprovals\Pages\ViewMyApproval;
use App\Models\SppbHeader;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MyApprovalResource extends Resource
{
    protected static ?string $model = SppbHeader::class;

    protected static ?string $slug = 'my-approvals';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static string|\UnitEnum|null $navigationGroup = 'Persetujuan';

    protected static ?string $navigationLabel = 'Kotak Masuk Saya';

    protected static ?string $modelLabel = 'Persetujuan SPPB';

    protected static ?string $pluralModelLabel = 'Persetujuan SPPB';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(3)->schema([
                Section::make('Detail SPPB')
                    ->schema([
                        Placeholder::make('document_number')
                            ->label('No. Dokumen')
                            ->content(fn ($record) => $record?->document_number ?? '-'),
                        Placeholder::make('requester.name')
                            ->label('Pemohon')
                            ->content(fn ($record) => $record?->requester?->name ?? '-'),
                        Placeholder::make('needed_name')
                            ->label('Keperluan')
                            ->content(fn ($record) => $record?->needed_name ?? '-'),
                    ])->columnSpan(2),
                Section::make('Histori Persetujuan')
                    ->schema([
                        Placeholder::make('approval_history')
                            ->label('')
                            ->content('Histori persetujuan akan tampil di sini.'),
                    ])->columnSpan(1),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('document_number')
                    ->label('No SPPB')
                    ->searchable(),
                TextColumn::make('requester.name')
                    ->label('Pemohon')
                    ->sortable(),
                TextColumn::make('department.name')
                    ->label('Departemen')
                    ->sortable(),
                TextColumn::make('needed_name')
                    ->label('Keperluan')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('SLA / Status')
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status', 'WAITING_APPROVAL');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMyApprovals::route('/'),
            'view' => ViewMyApproval::route('/{record}'),
        ];
    }
}
