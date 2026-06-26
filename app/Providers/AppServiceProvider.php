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
        $this->loadRoutesFrom(app_path('Modules/Home/Routes/web.php'));
        $this->loadViewsFrom(app_path('Modules/Home/Resources/views'), 'home');
        $this->loadTranslationsFrom(app_path('Modules/Home/Resources/lang'), 'home');
        $this->loadRoutesFrom(app_path('Modules/Consent/Routes/web.php'));
        $this->loadViewsFrom(app_path('Modules/Consent/Resources/views'), 'consent');
    }
}
