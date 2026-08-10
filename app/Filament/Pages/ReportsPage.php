<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\User;
use App\Services\Reporting\ReportAccessService;
use App\Services\Reporting\ReportExportService;
use App\Services\Reporting\ReportRegistry;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class ReportsPage extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Laporan';

    protected static ?string $title = 'Laporan Enterprise';

    protected static string|\UnitEnum|null $navigationGroup = 'Reporting';

    protected static ?int $navigationSort = 100;

    protected string $view = 'filament.pages.reports-page';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        $registry = app(ReportRegistry::class);

        return $form
            ->schema([
                Section::make('Kriteria Laporan')
                    ->schema([
                        Select::make('report_type')
                            ->label('Jenis Laporan')
                            ->options($registry->getOptions())
                            ->reactive()
                            ->afterStateUpdated(fn () => $this->resetTable())
                            ->columnSpan(1),

                        // Dynamic filter area based on selected report
                        Section::make('Filter')
                            ->schema(function (callable $get) use ($registry) {
                                $reportType = $get('report_type');
                                if (! $reportType || ! $registry->has($reportType)) {
                                    return [];
                                }

                                return $registry->get($reportType)->getFilterSchema();
                            })
                            ->columns(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        $reportType = $this->data['report_type'] ?? null;
        $registry = app(ReportRegistry::class);
        $accessService = app(ReportAccessService::class);

        if (! $reportType || ! $registry->has($reportType)) {
            return $table->query(fn () => User::query()->whereRaw('1 = 0'))->columns([]);
        }

        $report = $registry->get($reportType);
        $scope = $accessService->getScopeForUser();
        $filters = $this->data;

        $defaultSort = $report->getDefaultSorting();

        return $table
            ->query(function () use ($report, $scope, $filters) {
                return $report->getQuery($scope, $filters);
            })
            ->columns($report->getTableColumns())
            ->defaultSort($defaultSort['column'], $defaultSort['direction']);
    }

    public function filter(): void
    {
        $this->resetTable();
    }

    public function exportExcel(): mixed
    {
        try {
            return $this->executeExport('excel');
        } catch (\Exception $e) {
            Notification::make()->title('Export Failed')->body($e->getMessage())->danger()->send();

            return null;
        }
    }

    public function exportPdf(): mixed
    {
        try {
            return $this->executeExport('pdf');
        } catch (\Exception $e) {
            Notification::make()->title('Export Failed')->body($e->getMessage())->danger()->send();

            return null;
        }
    }

    protected function executeExport(string $type): mixed
    {
        $reportType = $this->data['report_type'] ?? null;
        $registry = app(ReportRegistry::class);

        if (! $reportType || ! $registry->has($reportType)) {
            throw new \Exception('Silakan pilih jenis laporan yang valid terlebih dahulu.');
        }

        $report = $registry->get($reportType);
        $scope = app(ReportAccessService::class)->getScopeForUser();
        $filters = $this->data;

        $exportService = app(ReportExportService::class);

        if ($type === 'excel') {
            return $exportService->exportExcel($report, $scope, $filters);
        }

        $res = $exportService->exportPdf($report, $scope, $filters);

        Notification::make()
            ->title(strtoupper($type).' Export Generated successfully')
            ->success()
            ->send();

        return $res;
    }
}
