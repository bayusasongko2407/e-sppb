<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('plant.name')
                    ->searchable(),
                TextColumn::make('department.name')
                    ->searchable(),
                TextColumn::make('positions.position.name')
                    ->label('Posisi')
                    ->badge()
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('manager.name')
                    ->searchable(),
                TextColumn::make('nik')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('No. WhatsApp')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('email_verified_at')
                    ->label('Email Diverifikasi Pada')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('last_login_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('failed_login_attempts')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('locked_until')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('position')
                    ->label('Posisi')
                    ->relationship('positions.position', 'name'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('resetPassword')
                    ->label('Reset Password')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->visible(fn (): bool => auth()->user()?->hasRole('super_admin') ?? false)
                    ->modalHeading('Reset Kata Sandi Pengguna')
                    ->modalSubmitActionLabel('Terapkan & Simpan')
                    ->form(function () {
                        $generatedPassword = self::generateIndonesianPassword();

                        return [
                            TextInput::make('new_password')
                                ->label('Kata Sandi Baru (Otomatis)')
                                ->default($generatedPassword)
                                ->readOnly()
                                ->helperText('Silakan salin kata sandi di atas untuk diberikan kepada pengguna. Klik "Terapkan & Simpan" untuk menyimpan perubahan.')
                                ->required(),
                        ];
                    })
                    ->action(function (User $record, array $data) {
                        $record->password = Hash::make($data['new_password']);
                        $record->save();

                        Notification::make()
                            ->title('Berhasil')
                            ->body("Kata sandi untuk {$record->name} berhasil di-reset.")
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function generateIndonesianPassword(): string
    {
        $words = [
            'kopi', 'gula', 'padi', 'buku', 'daun', 'kayu', 'awan', 'batu', 'pintu', 'kunci',
            'meja', 'kursi', 'kertas', 'pena', 'rumah', 'kucing', 'anjing', 'singa', 'macan',
            'elang', 'hiu', 'paus', 'lumba', 'semut', 'lebah', 'madu', 'pohon', 'bunga',
            'buah', 'akar', 'tanah', 'air', 'api', 'angin', 'langit', 'bintang', 'bulan',
            'matahari', 'pagi', 'siang', 'sore', 'malam', 'merah', 'biru', 'hijau', 'kuning',
            'putih', 'hitam', 'kelabu', 'emas', 'perak', 'besi', 'baja', 'kaca', 'plastik',
            'karet', 'kabel', 'listrik', 'lampu', 'kipas', 'mesin', 'mobil', 'motor', 'sepeda',
            'kapal', 'pesawat', 'kereta', 'jalan', 'lorong', 'jembatan', 'pasar', 'toko',
            'kantor', 'sekolah', 'kampus', 'masjid', 'gereja', 'candi', 'taman', 'hutan',
            'gunung', 'lembah', 'sungai', 'danau', 'laut', 'pantai', 'pulau',
        ];

        $word1 = $words[array_rand($words)];
        $word2 = $words[array_rand($words)];

        while ($word2 === $word1) {
            $word2 = $words[array_rand($words)];
        }

        $word1 = ucfirst($word1);
        $word2 = ucfirst($word2);
        $number = rand(100, 999);

        $symbols = ['!', '@', '#', '$', '%', '*', '?'];
        $symbol = $symbols[array_rand($symbols)];

        return "{$word1}{$word2}{$number}{$symbol}";
    }
}
