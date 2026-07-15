<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\DocumentRendererInterface;

class DummyDocumentRenderer implements DocumentRendererInterface
{
    public function renderToPdf(string $templatePath, array $data, array $options = []): string
    {
        // This is a dummy renderer that just outputs a mock PDF string.
        // In a real implementation, this would use Snappy, DomPDF, Browsershot, etc.
        $content = "%PDF-1.4\n";
        $content .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $content .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $content .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] >>\nendobj\n";
        $content .= "xref\n0 4\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n";
        $content .= "trailer\n<< /Size 4 /Root 1 0 R >>\nstartxref\n200\n%%EOF";

        // Append the payload just to make the hash unique per payload for testing
        $content .= "\n\nPayload: ".json_encode($data);

        return $content;
    }
}
