<?php

namespace App\Filament\Resources\Roles;

use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Models\Role;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static \UnitEnum|string|null $navigationGroup = 'Sistem & Konfigurasi';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Roles / Hak Akses';

    protected static ?string $modelLabel = 'Role';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        $modules = [
            'plant' => 'Plant (Pabrik)',
            'department' => 'Departemen',
            'location' => 'Lokasi Gudang',
            'unit' => 'Satuan Kerja / Unit',
            'position' => 'Jabatan',
            'user' => 'Pengguna (User)',
            'userposition' => 'Jabatan Pengguna',
            'item' => 'Barang (Item)',
            'asset' => 'Aset',
            'enumcontrol' => 'Master Klasifikasi (Enum)',
            'sppbheader' => 'Dokumen SPPB',
            'sppbdetail' => 'Detail Barang SPPB',
            'sppbstatuslog' => 'Log Status SPPB',
            'goodsrelease' => 'Pelepasan Barang (Surat Jalan)',
            'goodsreleaseitem' => 'Detail Rilis Barang',
            'activitylog' => 'Audit Log Aktivitas',
            'attachment' => 'Lampiran File',
            'documentaccess' => 'Hak Akses Dokumen',
            'emailchangerequest' => 'Permintaan Ubah Email',
            'apisetting' => 'Pengaturan API',
            'appsetting' => 'Pengaturan Aplikasi',
            'dataexport' => 'Ekspor Data',
            'dataimport' => 'Impor Data',
            'documenttemplate' => 'Templat Dokumen',
            'documentgeneration' => 'Generasi Dokumen',
            'documentpage' => 'Halaman Dokumen',
            'documentvalidation' => 'Validasi Dokumen',
            'workflowtemplate' => 'Master Workflow Template',
            'workflowstep' => 'Langkah Persetujuan',
            'workflowstepapprover' => 'Approver Workflow',
            'workflowinstance' => 'Instance Approval SPPB',
            'workflowinstancestep' => 'Log Approval Workflow',
            'workflowdelegation' => 'Delegasi Persetujuan',
            'workflowcommand' => 'Perintah Approval',
            'runningnumber' => 'Nomor Seri Dokumen (Sequence)',
            'legacyreference' => 'Referensi Warisan (Legacy)',
        ];

        $allPermissions = Permission::all();
        $checkboxes = [];
        $trackedPermissionIds = [];

        foreach ($modules as $module => $displayName) {
            $modulePermissions = $allPermissions->filter(function ($permission) use ($module) {
                $prefixes = ['view_any_', 'view_', 'create_', 'update_', 'delete_', 'restore_', 'force_delete_'];
                $modelName = $permission->name;
                foreach ($prefixes as $prefix) {
                    if (str_starts_with($permission->name, $prefix)) {
                        $modelName = substr($permission->name, strlen($prefix));
                        break;
                    }
                }

                return $modelName === $module;
            });

            $trackedPermissionIds = array_merge($trackedPermissionIds, $modulePermissions->pluck('id')->toArray());

            $options = $modulePermissions->mapWithKeys(function ($p) {
                $label = $p->name;
                if (str_starts_with($p->name, 'view_any_')) {
                    $label = 'Lihat Daftar';
                } elseif (str_starts_with($p->name, 'view_')) {
                    $label = 'Lihat Detail';
                } elseif (str_starts_with($p->name, 'create_')) {
                    $label = 'Tambah';
                } elseif (str_starts_with($p->name, 'update_')) {
                    $label = 'Ubah';
                } elseif (str_starts_with($p->name, 'delete_')) {
                    $label = 'Hapus';
                } elseif (str_starts_with($p->name, 'restore_')) {
                    $label = 'Pulihkan';
                } elseif (str_starts_with($p->name, 'force_delete_')) {
                    $label = 'Hapus Permanen';
                }

                return [$p->id => $label];
            })->toArray();

            if (empty($options)) {
                continue;
            }

            $checkboxes[] = Section::make($displayName)
                ->schema([
                    CheckboxList::make("permissions_{$module}")
                        ->label('')
                        ->options($options)
                        ->afterStateHydrated(function ($component, $record) use ($modulePermissions) {
                            if (! $record) {
                                return;
                            }
                            $rolePermissionIds = $record->permissions->pluck('id')->toArray();
                            $modulePermIds = $modulePermissions->pluck('id')->toArray();
                            $state = array_values(array_intersect($rolePermissionIds, $modulePermIds));
                            $component->state($state);
                        })
                        ->dehydrated(false)
                        ->bulkToggleable(),
                ])
                ->columnSpan(1);
        }

        // Dynamic fallback for unmapped or custom permissions
        $otherPermissions = $allPermissions->filter(fn ($p) => ! in_array($p->id, $trackedPermissionIds));

        if ($otherPermissions->isNotEmpty()) {
            $options = $otherPermissions->mapWithKeys(function ($p) {
                return [$p->id => str($p->name)->headline()->toString()];
            })->toArray();

            $checkboxes[] = Section::make('Fitur & Hak Akses Lainnya (Otomatis)')
                ->schema([
                    CheckboxList::make('permissions_other')
                        ->label('')
                        ->options($options)
                        ->afterStateHydrated(function ($component, $record) use ($otherPermissions) {
                            if (! $record) {
                                return;
                            }
                            $rolePermissionIds = $record->permissions->pluck('id')->toArray();
                            $otherPermIds = $otherPermissions->pluck('id')->toArray();
                            $state = array_values(array_intersect($rolePermissionIds, $otherPermIds));
                            $component->state($state);
                        })
                        ->dehydrated(false)
                        ->bulkToggleable(),
                ])
                ->columnSpan(1);
        }

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

                Grid::make(3)
                    ->schema($checkboxes)
                    ->hidden(fn (Get $get) => $get('is_superadmin'))
                    ->columnSpanFull(),

                Hidden::make('permissions_sync')
                    ->dehydrated(false)
                    ->saveRelationshipsUsing(function ($record, $get) use ($modules) {
                        if (! $record) {
                            return;
                        }
                        $allPermissionIds = [];
                        foreach (array_keys($modules) as $module) {
                            $ids = $get("permissions_{$module}") ?? [];
                            if (is_array($ids)) {
                                $allPermissionIds = array_merge($allPermissionIds, $ids);
                            }
                        }

                        $otherIds = $get('permissions_other') ?? [];
                        if (is_array($otherIds)) {
                            $allPermissionIds = array_merge($allPermissionIds, $otherIds);
                        }

                        $record->permissions()->sync($allPermissionIds);
                    }),
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
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }
}
