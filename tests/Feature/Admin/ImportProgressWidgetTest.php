<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Filament\Widgets\ImportProgressWidget;
use App\Models\User;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ImportProgressWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_progress_widget_displays_active_import_for_user(): void
    {
        $user = User::factory()->create();

        $import = Import::create([
            'completed_at' => null,
            'file_name' => 'test-assets.csv',
            'file_path' => '/tmp/test-assets.csv',
            'importer' => 'App\\Filament\\Imports\\AssetImporter',
            'processed_rows' => 50,
            'total_rows' => 100,
            'successful_rows' => 50,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        Livewire::test(ImportProgressWidget::class)
            ->assertSee('test-assets.csv')
            ->assertSee('50 / 100 baris')
            ->assertSee('50%');
    }

    public function test_user_can_dismiss_completed_import(): void
    {
        $user = User::factory()->create();

        $import = Import::create([
            'completed_at' => now(),
            'file_name' => 'test-completed.csv',
            'file_path' => '/tmp/test-completed.csv',
            'importer' => 'App\\Filament\\Imports\\AssetImporter',
            'processed_rows' => 100,
            'total_rows' => 100,
            'successful_rows' => 100,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        Livewire::test(ImportProgressWidget::class)
            ->call('dismissImport', $import->id)
            ->assertDontSee('test-completed.csv');

        $this->assertDatabaseMissing('imports', [
            'id' => $import->id,
        ]);
    }
}
