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
        $this->app->alias(\App\Pagination\CustomPaginator::class, \Illuminate\Pagination\LengthAwarePaginator::class); 
        $this->app->alias(\App\Pagination\CustomPaginator::class, \Illuminate\Pagination\LengthAwarePaginatorContract::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
