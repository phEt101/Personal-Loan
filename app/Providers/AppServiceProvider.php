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
        $this->loadRoutesFrom(app_path('Modules/Auth/Routes/web.php'));
        $this->loadViewsFrom(app_path('Modules/Auth/Resources/views'), 'auth');
        $this->loadTranslationsFrom(app_path('Modules/Auth/Resources/lang'), 'auth');

        $this->loadRoutesFrom(app_path('Modules/CustomerHistory/Routes/web.php'));
        $this->loadViewsFrom(app_path('Modules/CustomerHistory/Resources/views'), 'customerhistory');
        $this->loadTranslationsFrom(app_path('Modules/CustomerHistory/Resources/lang'), 'customerhistory');
    }
}
