<?php

namespace Laracasts\Cypress;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laracasts\Cypress\Controllers\CypressController;

class CypressServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->environment('production')) {
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
        Route::namespace(CypressController::class)
            ->middleware('web')
            ->group(__DIR__.'/routes/cypress.php');
    }
}
