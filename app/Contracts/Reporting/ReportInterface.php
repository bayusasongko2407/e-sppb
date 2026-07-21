<?php

declare(strict_types=1);

namespace App\Contracts\Reporting;

use App\DTOs\Reporting\ReportScope;
use Filament\Forms\Components\Component;
use Filament\Tables\Columns\Column;
use Illuminate\Database\Eloquent\Builder;

interface ReportInterface
{
    /**
     * Get the unique identifier for the report (e.g., 'sppb', 'asset').
     */
    public function getIdentifier(): string;

    /**
     * Get the display name of the report.
     */
    public function getName(): string;

    /**
     * Build the Filament form schema for the report's dynamic filters.
     *
     * @return array<Component>
     */
    public function getFilterSchema(): array;

    /**
     * Build the query for the report, applying the report scope and user filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getQuery(ReportScope $scope, array $filters): Builder;

    /**
     * Get the table columns for previewing the report in Filament.
     *
     * @return array<Column>
     */
    public function getTableColumns(): array;

    /**
     * Get the default sorting column and direction.
     *
     * @return array{column: string, direction: string}
     */
    public function getDefaultSorting(): array;
}
