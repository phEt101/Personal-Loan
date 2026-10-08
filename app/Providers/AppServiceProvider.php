<?php

namespace App\Providers;

use App\Modules\CustomerHistory\Console\Commands\PurgeTransferredAttachments;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->commands([
            PurgeTransferredAttachments::class,
        ]);
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

        $this->loadRoutesFrom(app_path('Modules/Report/Routes/web.php'));
        $this->loadViewsFrom(app_path('Modules/Report/Resources/views'), 'report');
        $this->loadTranslationsFrom(app_path('Modules/Report/Resources/lang'), 'report');

        $this->loadRoutesFrom(app_path('Modules/WorkDelegation/Routes/web.php'));
        $this->loadViewsFrom(app_path('Modules/WorkDelegation/Resources/views'), 'workdelegation');
        $this->loadTranslationsFrom(app_path('Modules/WorkDelegation/Resources/lang'), 'workdelegation');

        $this->loadRoutesFrom(app_path('Modules/Profile/Routes/web.php'));
        $this->loadViewsFrom(app_path('Modules/Profile/Resources/views'), 'profile');
        $this->loadTranslationsFrom(app_path('Modules/Profile/Resources/lang'), 'profile');

        $this->loadRoutesFrom(app_path('Modules/Settings/Routes/web.php'));
        $this->loadViewsFrom(app_path('Modules/Settings/Resources/views'), 'settings');
        $this->loadTranslationsFrom(app_path('Modules/Settings/Resources/lang'), 'settings');
    }
}
