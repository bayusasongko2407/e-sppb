<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\DocumentRendererInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class DummyDocumentRenderer implements DocumentRendererInterface
{
    public function renderToPdf(string $templatePath, array $data, array $options = []): string
    {
        $pdf = "%PDF-1.4\n";
        $pdf .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $pdf .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $pdf .= "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>\nendobj\n";
        $pdf .= "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";

        $stream = "BT\n";

        // Title
        $stream .= "/F4 16 Tf\n";
        $stream .= "50 740 Td\n";

        $title = 'DOKUMEN E-SPPB ENTERPRISE';
        if (isset($data['sppb_no'])) {
            $title = 'SURAT PERMOHONAN PENGIRIMAN BARANG';
        } elseif (isset($data['sj_no']) || isset($data['release_number'])) {
            $title = 'SURAT PELEPASAN BARANG (GOODS RELEASE)';
        }
        $stream .= '('.$title.") Tj\n";

        $stream .= "/F5 10 Tf\n";
        $stream .= "50 720 Td\n";

        // Render metadata fields
        $fields = [];
        if (isset($data['sppb_no'])) {
            $fields[] = ['Nomor SPPB', $data['sppb_no']];
            $fields[] = ['Tanggal', $data['request_date'] ?? '—'];
            $fields[] = ['Pemohon', $data['requester'] ?? '—'];
            $fields[] = ['Plant', $data['plant'] ?? '—'];
            $fields[] = ['Departemen', $data['department'] ?? '—'];
        } elseif (isset($data['release_number'])) {
            $fields[] = ['Nomor Pelepasan', $data['release_number']];
            $fields[] = ['Tanggal', $data['delivery_date'] ?? '—'];
            $fields[] = ['Pengirim', $data['sender_name'] ?? '—'];
            $fields[] = ['Penerima', $data['receiver_name'] ?? '—'];
        } else {
            // Generic fallback for test data
            foreach ($data as $key => $val) {
                if (is_scalar($val)) {
                    $fields[] = [ucfirst(str_replace('_', ' ', (string) $key)), (string) $val];
                }
            }
        }

        foreach ($fields as $field) {
            $label = $field[0].': '.$field[1];
            $label = str_replace(['(', ')'], ['\([', ']\)'], $label);
            $stream .= '('.$label.") Tj\n";
            $stream .= "0 -20 Td\n";
        }

        $stream .= "ET\n";

        // Drawing a line
        $stream .= "50 560 m 562 560 l S\n";

        // Draw QR Code if generation_uuid is present in options
        // The QR encodes the SHA256 verification URL for security
        $generationUuid = $options['generation_uuid'] ?? null;
        if ($generationUuid) {
            $pageNumber = 1;
            $sha256Token = DocumentVerificationService::deriveToken($generationUuid, $pageNumber);
            $verificationUrl = route('document.verify', ['sha256Token' => $sha256Token]);

            try {
                $qrOptions = new QROptions([
                    'version' => 5,
                    'eccLevel' => QRCode::ECC_H,
                ]);
                $matrixObj = (new QRCode($qrOptions))->getMatrix($verificationUrl);
                $matrix = $matrixObj->matrix();
                $matrixSize = count($matrix);

                $startX = 450;
                $startY = 60; // 60 points from the bottom edge
                $qrSize = 100;
                $cellSize = $qrSize / $matrixSize;

                // Set fill color to black for QR code modules
                $stream .= "0 g\n";

                for ($y = 0; $y < $matrixSize; $y++) {
                    for ($x = 0; $x < $matrixSize; $x++) {
                        if ($matrixObj->isDark($matrix[$y][$x])) {
                            $px = $startX + ($x * $cellSize);
                            $py = $startY + (($matrixSize - 1 - $y) * $cellSize);
                            $stream .= sprintf("%.2f %.2f %.2f %.2f re f\n", $px, $py, $cellSize, $cellSize);
                        }
                    }
                }

                // QR label text
                $stream .= "BT\n";
                $stream .= "/F5 7 Tf\n";
                $stream .= sprintf("%.2f %.2f Td\n", $startX, $startY - 10);
                $stream .= "(Scan untuk verifikasi dokumen) Tj\n";
                $stream .= "ET\n";

            } catch (\Throwable $e) {
                // Fallback gracefully on QR generation error — log but do not fail
            }
        }

        $stream .= "BT\n";
        $stream .= "/F4 12 Tf\n";
        $stream .= "50 540 Td\n";

        if (isset($data['details'])) {
            $stream .= "(DAFTAR BARANG:) Tj\n";
            $stream .= "/F5 10 Tf\n";

            foreach ($data['details'] as $index => $detail) {
                $itemText = ($index + 1).'. '.($detail['item_name'] ?? '—').' ('.($detail['quantity'] ?? '—').' '.($detail['unit'] ?? '').')';
                $itemText = str_replace(['(', ')'], ['\([', ']\)'], $itemText);
                $stream .= "0 -20 Td\n";
                $stream .= '('.$itemText.") Tj\n";

                if (! empty($detail['remarks'])) {
                    $remarksText = '   Catatan: '.$detail['remarks'];
                    $remarksText = str_replace(['(', ')'], ['\([', ']\)'], $remarksText);
                    $stream .= "0 -15 Td\n";
                    $stream .= '('.$remarksText.") Tj\n";
                }
            }
        } elseif (isset($data['items'])) {
            $stream .= "(DAFTAR ITEM PELEPASAN:) Tj\n";
            $stream .= "/F5 10 Tf\n";

            foreach ($data['items'] as $index => $item) {
                $itemText = ($index + 1).'. '.($item['item_name'] ?? '—').' ('.($item['quantity_released'] ?? '—').')';
                $itemText = str_replace(['(', ')'], ['\([', ']\)'], $itemText);
                $stream .= "0 -20 Td\n";
                $stream .= '('.$itemText.") Tj\n";
            }
        } else {
            $stream .= "(INFORMASI TAMBAHAN:) Tj\n";
            $stream .= "/F5 10 Tf\n";
            $stream .= "0 -20 Td\n";
            $stream .= "(Dokumen ini digenerate secara otomatis oleh sistem.) Tj\n";
        }

        $stream .= "ET\n";

        $streamLen = strlen($stream);

        $pdf .= "6 0 obj\n<< /Length $streamLen >>\nstream\n".$stream."endstream\nendobj\n";
        $pdf .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 6 0 R /Resources << /Font << /F4 4 0 R /F5 5 0 R >> >> >>\nendobj\n";

        $pdf .= "xref\n0 7\n0000000000 65535 f \n";
        $pdf .= "trailer\n<< /Size 7 /Root 1 0 R >>\nstartxref\n0\n%%EOF\n";

        return $pdf;
    }
}
