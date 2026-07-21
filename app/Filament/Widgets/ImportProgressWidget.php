<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Filament\Actions\Imports\Models\Import;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class ImportProgressWidget extends Widget
{
    protected string $view = 'filament.widgets.import-progress-widget';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->check();
    }

    public function getActiveImports(): Collection
    {
        $userId = auth()->id();

        if (! $userId) {
            return collect();
        }

        return Import::where('user_id', $userId)
            ->where(function ($query) {
                $query->whereNull('completed_at')
                    ->orWhere('updated_at', '>=', now()->subMinutes(15));
            })
            ->latest()
            ->take(5)
            ->get();
    }

    public function dismissImport(int $importId): void
    {
        $userId = auth()->id();

        Import::where('id', $importId)
            ->where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->delete();
    }
}
