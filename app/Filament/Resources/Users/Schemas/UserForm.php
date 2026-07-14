<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('plant_id')
                    ->label('Plant')
                    ->relationship('plant', 'name')
                    ->default(null),
                Select::make('department_id')
                    ->relationship('department', 'name')
                    ->default(null),
                Select::make('manager_id')
                    ->relationship('manager', 'name')
                    ->default(null),
                TextInput::make('nik')
                    ->required(),
                TextInput::make('name')
                    ->label('Nama')
                    ->required(),
                TextInput::make('email')
                    ->label('Email')
                    ->label('Email address')
                    ->email()
                    ->default(null),
                DateTimePicker::make('email_verified_at')
                    ->label('Email Diverifikasi Pada'),
                TextInput::make('password')
                    ->label('Kata Sandi')
                    ->password()
                    ->required(),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->required(),
                DateTimePicker::make('last_login_at'),
                TextInput::make('failed_login_attempts')
                    ->required()
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('locked_until'),
            ]);
    }
}
