<?php

declare(strict_types=1);

namespace App\Providers;

use App\Reports\SppbItemReport;
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

            // Register all reports here
            $registry->register(new SppbReport);
            $registry->register(new SppbItemReport);

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
