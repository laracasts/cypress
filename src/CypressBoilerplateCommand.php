<?php

namespace Laracasts\Cypress;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;

class CypressBoilerplateCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cypress:boilerplate { --config-path=cypress.config.js } { --force : Recreate existing configuration file }';

    /**
     * The console command description.
     */
    protected $description = 'Generate useful Cypress boilerplate.';

    /**
     * The path to the user's desired cypress install.
     */
    protected string $cypressPath;

    /**
     * Create a new Artisan command instance.
     */
    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    /**
     * Handle the command.
     */
    public function handle()
    {
        if (!$this->isCypressInstalled()) {
            $this->requireCypressInstall();

            return;
        }

        if (! $this->option('force') && $this->files->exists($this->cypressConfigPath())) {
            $this->warn('Existing Cypress configuration file found. Please upgrade the file manually or overwrite changes using --force.');

            return;
        }

        $this->cypressPath = trim(
            strtolower($this->ask('Where should we put the cypress directory?', 'tests/cypress')),
            '/'
        );

        $this->files->moveDirectory(base_path('cypress'), $this->cypressPath);

        $this->copyStubs();
    }

    /**
     * Copy the stubs from this package to the user's cypress folder.
     */
    protected function copyStubs(): void
    {
        $this->files->copyDirectory(__DIR__ . '/stubs/support', $this->cypressPath('support'));
        $this->files->copyDirectory(__DIR__ . '/stubs/plugins', $this->cypressPath('plugins'));
        $this->files->copyDirectory(__DIR__ . '/stubs/integration', $this->cypressPath('integration'));

        $this->lineBreak();

        $this->status('Updated', $this->cypressPath('support/index.js', false));
        $this->status('Updated', $this->cypressPath('plugins/index.js', false));
        $this->status('Created', $this->cypressPath('plugins/swap-env.js', false));
        $this->status('Created', $this->cypressPath('integration/example.cy.js', false));
        $this->status('Created', $this->cypressPath('support/laravel-commands.js', false));
        $this->status('Created', $this->cypressPath('support/laravel-routes.js', false));
        $this->status('Created', $this->cypressPath('support/assertions.js', false));
        $this->status('Created', $this->cypressPath('support/index.d.ts', false));

        $this->createCypressConfig();

        if (!$this->files->exists($path = base_path('.env.cypress'))) {
            $this->files->copy(base_path('.env'), $path);

            $this->status('Created', '.env.cypress');
        }

        $this->lineBreak();
    }

    /**
     * Set the initial cypress.config.js configuration for the project.
     */
    protected function createCypressConfig(): void
    {
        $config = [];

        $this->files->put(
            $this->cypressConfigPath(),
            $this->defaultCypressConfig()
        );

        $this->status('Created', $this->cypressConfigPath(false));
    }

    /**
     * Merge the user's current cypress.json config with this package's recommended defaults.
     */
    protected function defaultCypressConfig(array $config = []): string
    {
        return str_replace(
            [
                '%baseUrl%',
                '%cypressPath%',
            ],
            [
                config('app.url'),
                $this->cypressPath('', false)
            ],
            $this->files->get(__DIR__ . '/stubs/cypress.config.js')
        );
    }

    /**
     * Get the user-requested path to the Cypress directory.
     */
    protected function cypressPath(string $path = '', bool $absolute = true): string
    {
        $cypressPath = $absolute ? base_path($this->cypressPath) : $this->cypressPath;

        return $cypressPath . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }

    /**
     * Get the path to the cypress.json config file.
     */
    protected function cypressConfigPath(bool $absolute = true): string
    {
        return $absolute ? base_path($this->option('config-path')) : $this->option('config-path');
    }

    /**
     * Report the status of a file to the user.
     */
    protected function status(string $type, string $file)
    {
        $this->line("<info>{$type}</info> <comment>{$file}</comment>");
    }

    /**
     * Create a line break in the console.
     */
    protected function lineBreak(): void
    {
        $this->line('');
    }

    /**
     * Require that the user first install cypress through npm.
     */
    protected function requireCypressInstall()
    {
        $this->warn(
            <<<'EOT'

Cypress not found. Please install it through npm and try again.

npm install cypress --save-dev

EOT
        );
    }

    /**
     * Check if Cypress is added to the package.json file.
     */
    protected function isCypressInstalled()
    {
        $package = json_decode($this->files->get(base_path('package.json')), true);

        return Arr::get($package, 'devDependencies.cypress') || Arr::get($package, 'dependencies.cypress');
    }
}
