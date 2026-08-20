<?php

declare(strict_types=1);

namespace App\Filament\Resources\MyApprovals;

use App\Enums\ApproverStatus;
use App\Enums\SppbStatus;
use App\Filament\Resources\MyApprovals\Pages\ListMyApprovals;
use App\Filament\Resources\MyApprovals\Pages\ViewMyApproval;
use App\Filament\Resources\SppbHeaders\SppbHeaderResource;
use App\Models\SppbHeader;
use Carbon\CarbonInterface;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
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

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

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
                    ->badge()
                    ->formatStateUsing(function ($state, SppbHeader $record): string {
                        $statusLabel = $state instanceof SppbStatus
                            ? $state->label()
                            : (SppbStatus::tryFrom($state)?->label() ?? (string) $state);

                        $activeStep = $record->currentWorkflowInstance?->workflowInstanceSteps
                            ?->where('sequence', $record->current_step_sequence)
                            ->first();

                        if ($activeStep?->due_at) {
                            $dueAt = Carbon::parse($activeStep->due_at);
                            if ($dueAt->isPast()) {
                                $overdue = $dueAt->diffForHumans(['parts' => 1, 'syntax' => CarbonInterface::DIFF_ABSOLUTE]);

                                return "{$statusLabel} • Terlewat {$overdue}";
                            }

                            $remaining = $dueAt->diffForHumans(['parts' => 1, 'syntax' => CarbonInterface::DIFF_ABSOLUTE]);

                            return "{$statusLabel} • SLA: {$remaining}";
                        }

                        return $statusLabel;
                    })
                    ->color(fn ($state): string => $state instanceof SppbStatus
                        ? $state->color()
                        : (SppbStatus::tryFrom($state)?->color() ?? 'gray'))
                    ->icon(fn ($state): string => $state instanceof SppbStatus
                        ? $state->icon()
                        : (SppbStatus::tryFrom($state)?->icon() ?? 'heroicon-o-clock'))
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('revise')
                    ->label('Revisi')
                    ->color('warning')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn (SppbHeader $record): string => SppbHeaderResource::getUrl('edit', ['record' => $record]))
                    ->visible(fn (SppbHeader $record): bool => ($record->status === SppbStatus::REJECTED->value || $record->status === SppbStatus::REJECTED) && $record->requester_id === auth()->id()),
                ViewAction::make()
                    ->url(fn (SppbHeader $record): string => SppbHeaderResource::getUrl('view', ['record' => $record])),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getRecordRouteBindingQuery($record, string $property = 'record'): Builder
    {
        return parent::getEloquentQuery();
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        if (! $user) {
            return parent::getEloquentQuery()->whereRaw('1=0');
        }

        $userId = $user->id;
        $userRoleIds = $user->roles->pluck('id')->toArray();

        $isBatVerifier = false;
        try {
            $isBatVerifier = $user->hasAnyRole(['BAT Verifier', 'BAT', 'Verifikator BAT'])
                || $user->hasPermissionTo('verify_bat');
        } catch (\Throwable) {
            $isBatVerifier = $user->hasAnyRole(['BAT Verifier', 'BAT', 'Verifikator BAT']);
        }

        return parent::getEloquentQuery()
            ->with(['requester', 'department', 'currentWorkflowInstance.workflowInstanceSteps'])
            ->where(function (Builder $query) use ($user, $userId, $userRoleIds, $isBatVerifier) {
                // 1. Pending Approvals for current approver or workflow step approver
                $query->where('current_approver_id', $userId)
                    ->orWhereExists(function ($sub) use ($userId) {
                        $sub->select(DB::raw(1))
                            ->from('workflow_step_approvers')
                            ->join('workflow_instance_steps', 'workflow_step_approvers.workflow_instance_step_id', '=', 'workflow_instance_steps.id')
                            ->whereColumn('workflow_instance_steps.workflow_instance_id', '=', 'sppb_headers.current_workflow_instance_id')
                            ->whereColumn('workflow_instance_steps.sequence', '=', 'sppb_headers.current_step_sequence')
                            ->where('workflow_step_approvers.approver_id', $userId)
                            ->where('workflow_step_approvers.status', ApproverStatus::PENDING->value);
                    })
                    // 2. Rejected SPPB returned to Requester for revision
                    ->orWhere(function ($sub) use ($userId) {
                        $sub->where('requester_id', $userId)
                            ->where(function ($stQ) {
                                $stQ->where('status', SppbStatus::REJECTED->value)
                                    ->orWhere('status', 'REJECTED');
                            });
                    });

                // 3. Pending BAT verification for BAT Verifiers
                if ($isBatVerifier) {
                    $query->orWhere(function ($batQ) use ($user, $userRoleIds) {
                        $batQ->whereIn('status', [
                            SppbStatus::WAITING_VERIFICATION_BAT->value,
                            SppbStatus::PROCESS_VERIFICATION_BAT->value,
                            'WAITING_VERIFICATION_BAT',
                            'PROCESS_VERIFICATION_BAT',
                        ]);

                        if (! $user->hasRole('super_admin')) {
                            $batQ->where(function ($accessSub) use ($user, $userRoleIds) {
                                $accessSub->whereExists(function ($rawQuery) use ($user, $userRoleIds) {
                                    $rawQuery->select(DB::raw(1))
                                        ->from('document_accesses')
                                        ->where('document_accesses.module', 'sppb')
                                        ->where('document_accesses.can_view', true)
                                        ->where(function ($userOrRoleQ) use ($user, $userRoleIds) {
                                            $userOrRoleQ->where('document_accesses.user_id', $user->id);
                                            if (! empty($userRoleIds)) {
                                                $userOrRoleQ->orWhereIn('document_accesses.role_id', $userRoleIds);
                                            }
                                        })
                                        ->where(function ($plantQ) {
                                            $plantQ->whereColumn('document_accesses.plant_id', 'sppb_headers.plant_id')
                                                ->orWhereNull('document_accesses.plant_id');
                                        })
                                        ->where(function ($deptQ) {
                                            $deptQ->whereColumn('document_accesses.department_id', 'sppb_headers.department_id')
                                                ->orWhereNull('document_accesses.department_id');
                                        });
                                });

                                if ($user->plant_id) {
                                    $accessSub->orWhere('sppb_headers.plant_id', $user->plant_id);
                                }
                            });
                        }
                    });
                }
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
