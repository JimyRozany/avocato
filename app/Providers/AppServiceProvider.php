<?php

namespace App\Providers;

use App\Models\Legal;
use App\Observers\LegalObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Legal::observe(LegalObserver::class);
    }
}
