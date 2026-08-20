<?php

declare(strict_types=1);

namespace App\Providers;

use App\Reports\AssetMovementHistoryReport;
use App\Reports\DocumentValidationLogReport;
use App\Reports\GoodsReleaseSearchReport;
use App\Reports\ItemReceiptDiscrepancyReport;
use App\Reports\SppbItemFulfillmentReport;
use App\Reports\SppbReport;
use App\Services\Reporting\ReportRegistry;
use Illuminate\Support\ServiceProvider;

class ReportServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ReportRegistry::class, function ($app) {
            $registry = new ReportRegistry;

            // Register Reports 1 to 6
            $registry->register(new SppbReport);                        // 1. Matriks Master SPPB
            $registry->register(new GoodsReleaseSearchReport);         // 2. Surat Jalan & Status Pengiriman
            $registry->register(new DocumentValidationLogReport);      // 3. Keaslian & Scan QR Dokumen
            $registry->register(new SppbItemFulfillmentReport);       // 4. Rincian Pemenuhan Barang SPPB
            $registry->register(new ItemReceiptDiscrepancyReport);     // 5. Selisih & Kondisi Terima Barang
            $registry->register(new AssetMovementHistoryReport);      // 6. Tracing & Lokasi Aset Barcode

            return $registry;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
