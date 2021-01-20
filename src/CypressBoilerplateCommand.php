<?php

namespace Laracasts\Cypress;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class CypressBoilerplateCommand extends Command
{
    protected $signature = 'cypress:boilerplate {--cypressPath=cypress : Location of cypress folder}';

    protected $description = 'Generate useful Cypress boilerplate.';

    protected $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    public function handle()
    {
        if ($this->files->exists($this->cypressPath())) {
            $this->copyStubs();

            return;
        }

        $this->requestCypressInstallation();
    }

    protected function copyStubs()
    {
        $this->files->copyDirectory(__DIR__.'/stubs/support', $this->cypressPath('/support'));
        $this->files->copyDirectory(__DIR__.'/stubs/plugins', $this->cypressPath('/plugins'));

        $this->lineBreak();

        $this->status('Updated', $this->cypressPath('/support/index.js', false));
        $this->status('Updated', $this->cypressPath('/plugins/index.js', false));
        $this->status('Created', $this->cypressPath('/plugins/swap-env.js', false));
        $this->status('Created', $this->cypressPath('/support/laravel-commands.js', false));
        $this->status('Created', $this->cypressPath('/support/assertions.js', false));

        if (! $this->files->exists($path = base_path('.env.cypress'))) {
            $this->files->copy(base_path('.env'), $path);

            $this->status('Created', '.env.cypress');
        }

        $this->lineBreak();
    }

    protected function cypressPath($path = '', $includeBasePath = true){
        return base_path($this->option('cypressPath') . $path);
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
