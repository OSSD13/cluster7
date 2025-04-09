<?php

namespace App\Providers;

use App\Services\TrelloService;
use App\Helpers\DateHelper;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\SprintSettingsController;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TrelloService::class, function ($app) {
            return new TrelloService();
        });

        $this->app->singleton('datehelper', function () {
            return new DateHelper();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production') {
                URL::forceRootUrl(config('app.url'));
                URL::forceScheme('https');
        };

        // Add a custom Blade directive for formatting dates
        Blade::directive('formatDate', function ($expression) {
            return "<?php echo \App\Helpers\DateHelper::formatDate($expression); ?>";
        });

        // Add a custom Blade directive for formatting dates with time
        Blade::directive('formatDateTime', function ($expression) {
            return "<?php echo \App\Helpers\DateHelper::formatDateTime($expression); ?>";
        });

        // Add custom Blade directives for sprint dates
        Blade::directive('formatSprintDate', function ($expression) {
            return "<?php echo \App\Helpers\DateHelper::formatSprintDate($expression); ?>";
        });

        // Share sprint number with all views
        View::composer('*', function ($view) {
            $sprintSettingsController = new SprintSettingsController();
            $sprintNumber = $sprintSettingsController->getCurrentSprintNumber();
            $view->with('sprintNumber', $sprintNumber);
        });
    }
}
