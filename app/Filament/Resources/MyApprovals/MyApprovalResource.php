<?php

declare(strict_types=1);

namespace App\Filament\Resources\MyApprovals;

use App\Enums\ApproverStatus;
use App\Filament\Resources\MyApprovals\Pages\ListMyApprovals;
use App\Filament\Resources\MyApprovals\Pages\ViewMyApproval;
use App\Filament\Resources\SppbHeaders\SppbHeaderResource;
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
use Illuminate\Support\Facades\DB;

class MyApprovalResource extends Resource
{
    protected static ?string $model = SppbHeader::class;

    protected static ?string $slug = 'my-approvals';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-hand-raised';

    protected static string|\UnitEnum|null $navigationGroup = 'Transaksi';

    protected static ?int $navigationSort = 2;

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
                ViewAction::make()
                    ->url(fn (SppbHeader $record): string => SppbHeaderResource::getUrl('view', ['record' => $record])),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $userId = auth()->id();

        return parent::getEloquentQuery()
            ->whereExists(function ($query) use ($userId) {
                $query->select(DB::raw(1))
                    ->from('workflow_step_approvers')
                    ->join('workflow_instance_steps', 'workflow_step_approvers.workflow_instance_step_id', '=', 'workflow_instance_steps.id')
                    ->whereColumn('workflow_instance_steps.workflow_instance_id', '=', 'sppb_headers.current_workflow_instance_id')
                    ->whereColumn('workflow_instance_steps.sequence', '=', 'sppb_headers.current_step_sequence')
                    ->where('workflow_step_approvers.approver_id', $userId)
                    ->where('workflow_step_approvers.status', ApproverStatus::PENDING->value);
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMyApprovals::route('/'),
            'view' => ViewMyApproval::route('/{record}'),
        ];
    }
}
