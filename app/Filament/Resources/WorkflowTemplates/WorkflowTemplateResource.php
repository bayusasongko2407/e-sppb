<?php

declare(strict_types=1);

namespace App\Filament\Resources\WorkflowTemplates;

use App\Filament\Resources\WorkflowTemplates\Pages\CreateWorkflowTemplate;
use App\Filament\Resources\WorkflowTemplates\Pages\EditWorkflowTemplate;
use App\Filament\Resources\WorkflowTemplates\Pages\ListWorkflowTemplates;
use App\Filament\Resources\WorkflowTemplates\Pages\ViewWorkflowTemplate;
use App\Filament\Resources\WorkflowTemplates\Tables\WorkflowTemplatesTable;
use App\Models\WorkflowTemplate;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WorkflowTemplateResource extends Resource
{
    protected static ?string $model = WorkflowTemplate::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan Sistem';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('uuid')
                    ->label('UUID')
                    ->default(fn () => \Illuminate\Support\Str::uuid()->toString())
                    ->required()
                    ->hidden(),
                TextInput::make('code')
                    ->label('Kode Template')
                    ->placeholder('Contoh: SPPB-IT-001')
                    ->helperText('Kode unik identifikasi workflow. Maksimal 50 karakter.')
                    ->required()
                    ->maxLength(50),
                TextInput::make('name')
                    ->label('Nama Workflow')
                    ->placeholder('Contoh: Approval SPPB Departemen IT')
                    ->helperText('Nama deskriptif untuk alur persetujuan ini.')
                    ->required()
                    ->maxLength(150),
                TextInput::make('version')
                    ->label('Versi')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->hidden(),
                Select::make('plant_id')
                    ->label('Pabrik / Lokasi')
                    ->helperText('Kosongkan jika workflow ini berlaku untuk semua pabrik secara global.')
                    ->relationship('plant', 'name')
                    ->searchable()
                    ->preload()
                    ->default(null),
                Select::make('department_id')
                    ->label('Departemen')
                    ->helperText('Kosongkan jika workflow ini berlaku lintas departemen.')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload()
                    ->default(null),
                TextInput::make('document_type')
                    ->label('Jenis Dokumen')
                    ->required()
                    ->default('SPPB')
                    ->readOnly(),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->default(null)
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true)
                    ->required(),
                DateTimePicker::make('effective_from')
                    ->label('Berlaku Dari'),
                DateTimePicker::make('effective_until')
                    ->label('Berlaku Sampai'),
                Repeater::make('workflowSteps')
                    ->label('Langkah Workflow')
                    ->relationship('workflowSteps')
                    ->schema([
                        TextInput::make('sequence')
                            ->label('Urutan Ke-')
                            ->helperText('Nomor urut langkah persetujuan (Contoh: 1).')
                            ->numeric()
                            ->required(),
                        TextInput::make('code')
                            ->label('Kode Langkah')
                            ->placeholder('Contoh: CHECKER-1')
                            ->helperText('Kode unik untuk langkah ini.')
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama Langkah')
                            ->placeholder('Contoh: Pengecekan Gudang')
                            ->helperText('Penjelasan singkat tentang tugas persetujuan ini.')
                            ->required(),
                        Select::make('approver_type')
                            ->label('Tipe Penyetuju')
                            ->helperText('Tentukan siapa yang berhak menyetujui langkah ini.')
                            ->options([
                                'USER' => 'Pengguna Tertentu (User)',
                                'POSITION' => 'Berdasarkan Jabatan (Position)',
                            ])
                            ->required()
                            ->live(),
                        Select::make('approver_user_id')
                            ->label('Pengguna')
                            ->relationship('approverUser', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn ($get) => $get('approver_type') === 'USER')
                            ->required(fn ($get) => $get('approver_type') === 'USER'),
                        Select::make('approver_position_id')
                            ->label('Jabatan')
                            ->relationship('approverPosition', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn ($get) => $get('approver_type') === 'POSITION')
                            ->required(fn ($get) => $get('approver_type') === 'POSITION'),
                        Select::make('approval_mode')
                            ->label('Aturan Persetujuan')
                            ->helperText('ANY: Cukup 1 orang setuju. ALL: Semua orang di jabatan ini harus setuju.')
                            ->options([
                                'ANY' => 'Cukup Salah Satu (ANY)',
                                'ALL' => 'Wajib Semuanya (ALL)',
                            ])
                            ->default('ANY')
                            ->required(),
                        TextInput::make('minimum_approvals')
                            ->label('Minimal Persetujuan')
                            ->helperText('Jumlah minimal orang yang harus menyetujui (biasanya 1).')
                            ->numeric()
                            ->default(1)
                            ->required(),
                        TextInput::make('sla_hours')
                            ->label('Batas Waktu (Jam)')
                            ->helperText('Waktu SLA persetujuan (Contoh: 24 untuk satu hari).')
                            ->numeric()
                            ->default(24)
                            ->required(),
                        Toggle::make('allow_self_approval')
                            ->label('Izinkan Setujui Sendiri')
                            ->default(false),
                    ])
                    ->orderColumn('sequence')
                    ->defaultItems(1)
                    ->collapsible()
                    ->columns(2)
                    ->columnSpanFull(),
            ])->columns(2);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('uuid')
                    ->label('UUID'),
                TextEntry::make('code'),
                TextEntry::make('name'),
                TextEntry::make('version')
                    ->numeric(),
                TextEntry::make('plant.name')
                    ->label('Plant')
                    ->placeholder('-'),
                TextEntry::make('department.name')
                    ->label('Department')
                    ->placeholder('-'),
                TextEntry::make('document_type'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('effective_from')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('effective_until')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return WorkflowTemplatesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkflowTemplates::route('/'),
            'create' => CreateWorkflowTemplate::route('/create'),
            'view' => ViewWorkflowTemplate::route('/{record}'),
            'edit' => EditWorkflowTemplate::route('/{record}/edit'),
        ];
    }
}
