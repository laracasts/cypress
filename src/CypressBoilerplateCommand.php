<?php

namespace Laracasts\Cypress;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CypressBoilerplateCommand extends Command
{
    protected $signature = 'cypress:boilerplate';

    protected $description = 'Generate useful Cypress boilerplate.';

    public function handle()
    {
        if (! File::exists(base_path('cypress'))) {
            $this->warn(
                <<<EOT

                Cypress not found. Please install it through npm and try again.

                npm install cypress --save-dev && npx cypress open

                EOT
            );

            return;
        }

        $this->copyStubs();
    }

    protected function copyStubs()
    {
        File::copyDirectory(__DIR__.'/stubs/support', base_path('cypress/support'));
        File::copyDirectory(__DIR__.'/stubs/plugins', base_path('cypress/plugins'));

        $this->info('Updated: ' . 'cypress/support/index.js');
        $this->info('Updated: ' . 'cypress/plugins/index.js');
        $this->info('Created: ' . 'cypress/plugins/swap-env.js');
        $this->info('Created: ' . 'cypress/support/laravel-commands.js');
        $this->info('Created: ' . 'cypress/support/assertions.js');

        if (! File::exists($path = base_path('.env.cypress'))) {
            File::copy(base_path('.env'), $path);

            $this->info('Created: .env.cypress');
        }
    }
}
