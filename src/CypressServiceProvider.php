<?php

namespace Laracasts\Cypress;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CypressServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/cypress.php', 'cypress'
        );

        $excludedEnvironments = config('cypress.exclude');
        if (is_string($excludedEnvironments)) {
            $excludedEnvironments = explode(',', $excludedEnvironments);
        }
        $excludedEnvironments[] = 'production';

        if ($this->app->environment($excludedEnvironments)) {
            return;
        }

        $this->addRoutes();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/routes/cypress.php' => base_path('routes/cypress.php'),
            ]);

            $this->commands([
                CypressBoilerplateCommand::class,
            ]);
        }
    }

    protected function addRoutes()
    {
        Route::namespace('')
            ->middleware('web')
            ->group(__DIR__.'/routes/cypress.php');
    }
}
