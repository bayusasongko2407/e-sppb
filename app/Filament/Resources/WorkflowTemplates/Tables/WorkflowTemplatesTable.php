<?php

namespace App\Filament\Resources\WorkflowTemplates\Tables;

use App\Filament\Resources\WorkflowTemplates\WorkflowTemplateResource;
use App\Models\WorkflowTemplate;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class WorkflowTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('uuid')
                    ->label('UUID'),
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('version')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('plant.name')
                    ->searchable(),
                TextColumn::make('department.name')
                    ->searchable(),
                TextColumn::make('document_type')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('effective_from')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('effective_until')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('duplicate')
                    ->label('Salin')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->url(fn (WorkflowTemplate $record): string => WorkflowTemplateResource::getUrl('create', [
                        'source' => $record->id,
                    ])),
                DeleteAction::make()
                    ->before(function (DeleteAction $action, WorkflowTemplate $record): void {
                        if ($record->hasDependentRecords()) {
                            Notification::make()
                                ->danger()
                                ->title('Gagal Menghapus Template Workflow')
                                ->body('Template workflow tidak dapat dihapus karena masih digunakan oleh dokumen SPPB / alur persetujuan aktif.')
                                ->send();

                            $action->halt();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function (DeleteBulkAction $action, Collection $records): void {
                            foreach ($records as $record) {
                                if ($record->hasDependentRecords()) {
                                    Notification::make()
                                        ->danger()
                                        ->title('Gagal Menghapus Template Workflow')
                                        ->body("Template workflow '{$record->name}' tidak dapat dihapus karena masih digunakan oleh alur persetujuan aktif.")
                                        ->send();

                                    $action->halt();
                                }
                            }
                        }),
                ]),
            ]);
    }
}
