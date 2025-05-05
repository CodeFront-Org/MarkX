<?php

namespace App\Providers;

use App\Models\Invoice;
use App\Models\Quote;
use App\Policies\InvoicePolicy;
use App\Policies\QuotePolicy;
use App\Policies\ReportPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Invoice::class => InvoicePolicy::class,
        Quote::class => QuotePolicy::class,
        'App\Models\Report' => ReportPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('view-reports', function ($user) {
            return $user->role === 'manager';
        });
    }
}
