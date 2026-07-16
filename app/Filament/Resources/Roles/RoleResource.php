<?php

namespace App\Filament\Resources\Roles;

use App\Filament\Resources\Roles\Pages\ManageRoles;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static \UnitEnum|string|null $navigationGroup = 'Sistem';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Roles / Hak Akses';

    protected static ?string $modelLabel = 'Role';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Toggle::make('is_superadmin')
                    ->label('Super Admin (Akses Penuh)')
                    ->helperText('Jika diaktifkan, role ini akan memiliki hak akses penuh ke seluruh sistem.')
                    ->live()
                    ->dehydrated(false)
                    ->afterStateHydrated(function ($component, $state, $record) {
                        $component->state($record?->name === 'super_admin');
                    })
                    ->afterStateUpdated(function ($state, Set $set) {
                        if ($state) {
                            $set('name', 'super_admin');
                        } else {
                            $set('name', '');
                        }
                    }),
                TextInput::make('name')
                    ->label('Nama Role')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->disabled(fn (Get $get) => $get('is_superadmin'))
                    ->dehydrated()
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                        if ($get('is_superadmin')) {
                            $set('name', 'super_admin');
                        }
                    }),
                Select::make('permissions')
                    ->label('Izin Modul (Hak Akses)')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->options(function () {
                        return Permission::all()
                            ->groupBy(function ($permission) {
                                $parts = explode('.', $permission->name);
                                if (count($parts) > 1) {
                                    return strtoupper(str_replace('_', ' ', $parts[0]));
                                }

                                return 'LAINNYA';
                            })
                            ->map(function ($group) {
                                return $group->pluck('name', 'id')->toArray();
                            })
                            ->toArray();
                    })
                    ->afterStateHydrated(function ($component, $state, $record) {
                        if ($record) {
                            $component->state($record->permissions->pluck('id')->toArray());
                        }
                    })
                    ->saveRelationshipsUsing(function ($component, $state, $record) {
                        if ($record) {
                            $record->permissions()->sync($state ?? []);
                        }
                    })
                    ->dehydrated(false)
                    ->hidden(fn (Get $get) => $get('is_superadmin'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRoles::route('/'),
        ];
    }
}
