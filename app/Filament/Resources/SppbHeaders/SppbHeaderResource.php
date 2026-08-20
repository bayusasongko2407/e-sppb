<?php

declare(strict_types=1);

namespace App\Filament\Resources\SppbHeaders;

use App\Filament\Resources\SppbHeaders\Pages\CreateSppbHeader;
use App\Filament\Resources\SppbHeaders\Pages\EditSppbHeader;
use App\Filament\Resources\SppbHeaders\Pages\ListSppbHeaders;
use App\Filament\Resources\SppbHeaders\Pages\ViewSppbHeader;
use App\Filament\Resources\SppbHeaders\Schemas\SppbHeaderForm;
use App\Filament\Resources\SppbHeaders\Schemas\SppbHeaderInfolist;
use App\Filament\Resources\SppbHeaders\Tables\SppbHeadersTable;
use App\Models\SppbHeader;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class SppbHeaderResource extends Resource
{
    protected static ?string $model = SppbHeader::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Transaksi';

    protected static ?string $modelLabel = 'Dokumen SPPB';

    protected static ?string $pluralModelLabel = 'Dokumen SPPB';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return SppbHeaderForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SppbHeaderInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SppbHeadersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSppbHeaders::route('/'),
            'create' => CreateSppbHeader::route('/create'),
            'view' => ViewSppbHeader::route('/{record}'),
            'edit' => EditSppbHeader::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery();

        if (! $user) {
            return $query;
        }

        if ($user->hasRole('super_admin')) {
            return $query;
        }

        $userRoleIds = $user->roles->pluck('id')->toArray();

        return $query->where(function (Builder $q) use ($user, $userRoleIds) {
            $q->where('requester_id', $user->id)
                ->orWhere('current_approver_id', $user->id)
                ->orWhereHas('currentWorkflowInstance.workflowInstanceSteps', function ($stepQ) use ($user) {
                    $stepQ->where('status', 'PENDING')
                        ->whereHas('stepApprovers', function ($appQ) use ($user) {
                            $appQ->where('approver_id', $user->id)->where('status', 'PENDING');
                        });
                })
                ->orWhere(function (Builder $sub) use ($user, $userRoleIds) {
                    $sub->whereExists(function ($rawQuery) use ($user, $userRoleIds) {
                        $rawQuery->select(DB::raw(1))
                            ->from('document_accesses')
                            ->where('document_accesses.module', 'sppb')
                            ->where(function ($actQ) {
                                $actQ->where('document_accesses.can_view', true)
                                    ->orWhere('document_accesses.can_create', true)
                                    ->orWhere('document_accesses.can_edit', true)
                                    ->orWhere('document_accesses.can_delete', true);
                            })
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
                });

            if ($user->plant_id) {
                $q->orWhere(function ($userPlantQ) use ($user) {
                    $userPlantQ->where('plant_id', $user->plant_id);
                    if ($user->department_id) {
                        $userPlantQ->where('department_id', $user->department_id);
                    }
                });
            }
        });
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
