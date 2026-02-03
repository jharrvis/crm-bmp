<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Route;
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
        // Explicit route model binding: 'employee' parameter should use User model
        Route::model('employee', User::class);

        // Share branches globally validation
        if (\Illuminate\Support\Facades\Schema::hasTable('branches')) {
            try {
                \Illuminate\Support\Facades\View::share('global_branches', \App\Models\Branch::all());
            } catch (\Exception $e) {
                // Prevent crash during migration if table exists but empty or other issue
            }
        }
    }
}
