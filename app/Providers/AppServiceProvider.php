<?php

namespace App\Providers;

use App\Contracts\ErrorHandlerInterface;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\Tenant;
use App\Observers\PlanFeatureObserver;
use App\Observers\PlanObserver;
use App\Observers\TenantObserver;
use App\Services\ApprovalWorkflow\ApprovalWorkflowInterface;
use App\Services\ApprovalWorkflow\ApprovalWorkflowService;
use App\Services\Exceptions\ExecuteWithErrorHandling;
use App\Services\MicrosoftGraphBaseService;
use App\Services\OrganizationCenter\Tasks\Task\TaskService;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Vite;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MicrosoftGraphBaseService::class, function ($app) {
            return new MicrosoftGraphBaseService();
        });

        // $this->app->singleton(TaskService::class, function ($app) {
        //     return new TaskService($app->make(MicrosoftGraphBaseService::class));
        // });

        // لتسجتيل الاخطاء
        $this->app->bind(
            ErrorHandlerInterface::class,
            ExecuteWithErrorHandling::class
        );

        $this->app->bind(
            ApprovalWorkflowInterface::class,
            ApprovalWorkflowService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('ar');

        Paginator::useBootstrapFive();

        // Phase 4 — billing observers
        Tenant::observe(TenantObserver::class);
        Plan::observe(PlanObserver::class);
        PlanFeature::observe(PlanFeatureObserver::class);


        Vite::useStyleTagAttributes(function (?string $src, string $url, ?array $chunk, ?array $manifest) {
            if ($src !== null) {
                return [
                    'class' => preg_match("/(resources\/assets\/vendor\/scss\/(rtl\/)?core)-?.*/i", $src) ? 'template-customizer-core-css' : (preg_match("/(resources\/assets\/vendor\/scss\/(rtl\/)?theme)-?.*/i", $src) ? 'template-customizer-theme-css' : '')
                ];
            }
            return [];
        });
    }
}
