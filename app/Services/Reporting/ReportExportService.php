<?php

declare(strict_types=1);

namespace App\Services\Reporting;

use App\Contracts\Reporting\ReportInterface;
use App\DTOs\Document\DocumentGenerationData;
use App\DTOs\Reporting\ReportScope;
use App\Models\DocumentTemplate;
use App\Services\DocumentGenerationService;
use Exception;

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
        // TODO: Integrate with Maatwebsite Excel or Spatie Simple Excel
        // The query is built strictly once by the Report:
        $query = $report->getQuery($scope, $filters);

        throw new Exception('Excel export is not fully implemented yet, pending specific package integration.');
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
