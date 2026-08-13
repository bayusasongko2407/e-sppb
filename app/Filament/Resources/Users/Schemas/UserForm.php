<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('plant_id')
                    ->label('Plant')
                    ->relationship('plant', 'name')
                    ->default(null)
                    ->live(),
                Select::make('department_id')
                    ->label('Departemen')
                    ->relationship('department', 'name', fn ($query, $get) => $query->when($get('plant_id'), fn ($q, $plantId) => $q->where('plant_id', $plantId)))
                    ->default(null),
                Select::make('manager_id')
                    ->label('Atasan Direct / Manager')
                    ->relationship('manager', 'name')
                    ->default(null),
                TextInput::make('nik')
                    ->label('NIK / Nomor Induk')
                    ->required(),
                TextInput::make('name')
                    ->label('Nama')
                    ->required(),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->default(null),
                TextInput::make('phone')
                    ->label('Nomor Telepon / WhatsApp')
                    ->tel()
                    ->placeholder('Contoh: 081234567890')
                    ->helperText('Nomor HP/WA aktif untuk penerimaan notifikasi WhatsApp OpenWA.')
                    ->default(null),
                TextInput::make('password')
                    ->label('Kata Sandi')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create'),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true)
                    ->required(),
                Select::make('roles')
                    ->label('Peran (Role)')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
                Repeater::make('positions')
                    ->label('Posisi (Positions)')
                    ->relationship('positions')
                    ->schema([
                        Select::make('position_id')
                            ->label('Posisi')
                            ->relationship('position', 'name')
                            ->required(),
                        Toggle::make('is_primary')
                            ->label('Utama')
                            ->default(false),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }
}
