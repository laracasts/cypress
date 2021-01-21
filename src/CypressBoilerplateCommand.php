<?php

namespace Laracasts\Cypress;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class CypressBoilerplateCommand extends Command
{
    protected $signature = 'cypress:boilerplate';

    protected $description = 'Generate useful Cypress boilerplate.';

    protected $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    public function handle()
    {
        if ($this->files->exists(base_path('cypress'))) {
            $this->copyStubs();

            return;
        }

        $this->requestCypressInstallation();
    }

    protected function copyStubs()
    {
        $this->files->copyDirectory(
            __DIR__ . '/stubs/support',
            base_path('cypress/support')
        );
        $this->files->copyDirectory(
            __DIR__ . '/stubs/plugins',
            base_path('cypress/plugins')
        );

        $this->lineBreak();

        $this->status('Updated', 'cypress/support/index.js');
        $this->status('Updated', 'cypress/plugins/index.js');
        $this->status('Created', 'cypress/plugins/swap-env.js');
        $this->status('Created', 'cypress/support/laravel-commands.js');
        $this->status('Created', 'cypress/support/laravel-routes.js');
        $this->status('Created', 'cypress/support/assertions.js');

        if (!$this->files->exists($path = base_path('.env.cypress'))) {
            $this->files->copy(base_path('.env'), $path);

            $this->status('Created', '.env.cypress');
        }

        $this->lineBreak();
    }

    protected function status($type, $file)
    {
        $this->line("<info>{$type}</info> <comment>{$file}</comment>");
    }

    protected function lineBreak()
    {
        $this->line('');
    }

    protected function requestCypressInstallation()
    {
        $this->warn(
            <<<'EOT'

Cypress not found. Please install it through npm and try again.

npm install cypress --save-dev && npx cypress open

EOT
        );
    }
}
