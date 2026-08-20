<?php

declare(strict_types=1);

namespace App\Services\Reporting;

use App\Contracts\Reporting\ReportInterface;
use App\DTOs\Document\DocumentGenerationData;
use App\DTOs\Reporting\ReportScope;
use App\Enums\SppbStatus;
use App\Models\DocumentTemplate;
use App\Services\DocumentGenerationService;
use Exception;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportExportService
{
    public function __construct(
        protected DocumentGenerationService $documentGenerationService
    ) {}

    /**
     * Export the report to Excel.
     */
    public function exportExcel(ReportInterface $report, ReportScope $scope, array $filters): mixed
    {
        $query = $report->getQuery($scope, $filters);
        $records = $query->get();
        $columns = $report->getTableColumns();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($report->getName(), 0, 31));

        // Header Row
        $colIndex = 1;
        foreach ($columns as $column) {
            $sheet->setCellValue([$colIndex, 1], $column->getLabel());
            $colIndex++;
        }

        // Header Styling (Bold, Dark Gray Header, White Text)
        $lastColLetter = Coordinate::stringFromColumnIndex(count($columns));
        $headerRange = "A1:{$lastColLetter}1";
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new Color('FFFFFF'));
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('4A5568');

        // Data Rows
        $rowIndex = 2;
        foreach ($records as $record) {
            $colIndex = 1;
            foreach ($columns as $column) {
                $val = $this->getColumnValue($column, $record);
                $sheet->setCellValue([$colIndex, $rowIndex], $val);
                $colIndex++;
            }
            $rowIndex++;
        }

        // Auto-fit Column Widths
        for ($i = 1; $i <= count($columns); $i++) {
            $colLetter = Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $filename = 'Laporan_'.str_replace(' ', '_', $report->getIdentifier()).'_'.date('Ymd_His').'.xlsx';

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    protected function getColumnValue(TextColumn $column, mixed $record): string
    {
        $stateUsingProp = new \ReflectionProperty($column, 'getStateUsing');
        $stateUsingProp->setAccessible(true);
        $stateUsing = $stateUsingProp->getValue($column);

        if ($stateUsing instanceof \Closure) {
            $value = $stateUsing($record);
        } else {
            $name = $column->getName();
            $value = data_get($record, $name);
        }

        $formatStateUsingProp = new \ReflectionProperty($column, 'formatStateUsing');
        $formatStateUsingProp->setAccessible(true);
        $formatUsing = $formatStateUsingProp->getValue($column);

        if ($formatUsing instanceof \Closure) {
            try {
                $value = $formatUsing($value, $record, $column);
            } catch (\Throwable) {
                try {
                    $value = $formatUsing($value);
                } catch (\Throwable) {
                    // keep raw value
                }
            }
        }

        if ($value instanceof SppbStatus) {
            $value = $value->label();
        }

        if ($value instanceof Carbon || $value instanceof \DateTimeInterface) {
            $carbon = Carbon::instance($value)->setTimezone('Asia/Jakarta');
            $value = ($carbon->hour !== 0 || $carbon->minute !== 0 || $carbon->second !== 0)
                ? $carbon->format('d/m/Y H:i:s')
                : $carbon->format('d/m/Y');
        }

        if (is_array($value)) {
            $value = implode(', ', $value);
        }

        return (string) ($value ?? '-');
    }

    /**
     * Export the report to PDF.
     * Uses DocumentGenerationService as the foundation for the export.
     */
    public function exportPdf(ReportInterface $report, ReportScope $scope, array $filters): mixed
    {
        $query = $report->getQuery($scope, $filters);

        // Example integration:
        // In reality, you'd need a specific DocumentTemplate for Reports.
        // And 'plantId' might need to be resolved from user's primary plant or omitted if not applicable,
        // but DocumentGenerationData requires plantId for now.

        // Fetching the template for this report (assuming a convention)
        $template = DocumentTemplate::where('document_type', 'REPORT_'.strtoupper($report->getIdentifier()))
            ->where('is_active', true)
            ->latest('version')
            ->first();

        if (! $template) {
            throw new Exception("Document Template for report [{$report->getIdentifier()}] not found.");
        }

        $plantId = auth()->user()->plant_id ?? 1; // Fallback to user's plant

        $data = new DocumentGenerationData(
            documentType: 'REPORT_'.strtoupper($report->getIdentifier()),
            templateId: $template->id,
            plantId: $plantId,
            generatedById: auth()->id() ?? 1,
            renderPayload: [
                'filters' => $filters,
                // We pass the raw filters. The background job will re-evaluate the query.
            ],
            isOfficial: false
        );

        return $this->documentGenerationService->requestGeneration($data);
    }
}
