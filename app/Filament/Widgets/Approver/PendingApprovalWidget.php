<?php

declare(strict_types=1);

namespace App\Filament\Widgets\Approver;

use App\Enums\SppbStatus;
use App\Models\SppbHeader;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingApprovalWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return $user->hasRole('super_admin') || $user->can('viewAny', SppbHeader::class);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                SppbHeader::query()
                    ->whereHas('currentWorkflowInstance.workflowInstanceSteps.stepApprovers', function ($query) {
                        $query->where('approver_id', auth()->id())
                            ->where('status', 'PENDING');
                    })
                    ->with(['plant', 'department', 'requester'])
                    ->latest()
                    ->limit(5)
            )
            ->heading('SPPB Menunggu Persetujuan Saya')
            ->columns([
                Tables\Columns\TextColumn::make('sppb_no')
                    ->label('Nomor SPPB')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('requester.name')
                    ->label('Pemohon'),
                Tables\Columns\TextColumn::make('plant.name')
                    ->label('Pabrik'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => SppbStatus::tryFrom($state)?->color() ?? 'gray')
                    ->icon(fn (string $state): string => SppbStatus::tryFrom($state)?->icon() ?? 'heroicon-o-question-mark-circle')
                    ->formatStateUsing(fn (string $state) => SppbStatus::tryFrom($state)?->label() ?? $state),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diajukan Pada')
                    ->dateTime('d M Y H:i'),
            ])
            ->paginated(false);
    }
}
