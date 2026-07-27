<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\DocumentRendererInterface;
use App\Contracts\SppbServiceContract;
use App\Contracts\WorkflowServiceContract;
use App\Models\Role;
use App\Policies\RolePolicy;
use App\Services\DummyDocumentRenderer;
use App\Services\RunningNumberService;
use App\Services\SppbService;
use App\Services\Workflow\ApproverResolver;
use App\Services\Workflow\WorkflowTemplateResolver;
use App\Services\WorkflowService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            DocumentRendererInterface::class,
            DummyDocumentRenderer::class
        );

        $this->app->singleton(
            WorkflowServiceContract::class,
            WorkflowService::class,
        );

        $this->app->singleton(
            SppbServiceContract::class,
            SppbService::class,
        );

        $this->app->singleton(RunningNumberService::class);

        $this->app->singleton(WorkflowTemplateResolver::class);

        $this->app->singleton(ApproverResolver::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        URL::forceScheme('https');

        Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });

        Gate::policy(Role::class, RolePolicy::class);
    }
}
