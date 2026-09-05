<?php

namespace App\Providers;

use App\Helpers\Qs;
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
        if (! class_exists('Qs')) {
            class_alias(Qs::class, 'Qs');
        }
    }
}
