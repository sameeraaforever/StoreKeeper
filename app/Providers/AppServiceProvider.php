<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register the ModelAuditor for models you want audited
        $auditor = \App::make(\App\Observers\ModelAuditor::class);

        // Auditing SupplyRecord, ProductPrice, Product, Company, Location (as required)
        \App\Models\SupplyRecord::observe($auditor);
        \App\Models\Product::observe($auditor);
        \App\Models\ProductPrice::observe($auditor);
        \App\Models\Company::observe($auditor);
        \App\Models\Location::observe($auditor);
    }
}
