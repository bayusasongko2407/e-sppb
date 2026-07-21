<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\ReportServiceProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    ReportServiceProvider::class,
];
