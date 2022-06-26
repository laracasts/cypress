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
    protected $signature = 'cypress:boilerplate { --config-path=cypress.json }';

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
        if (! $this->isCypressInstalled()) {
            $this->requireCypressInstall();

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

        $this->lineBreak();

        $this->status('Updated', $this->cypressPath('support/index.js', false));
        $this->status('Updated', $this->cypressPath('plugins/index.js', false));
        $this->status('Created', $this->cypressPath('plugins/swap-env.js', false));
        $this->status('Created', $this->cypressPath('support/laravel-commands.js', false));
        $this->status('Created', $this->cypressPath('support/laravel-routes.js', false));
        $this->status('Created', $this->cypressPath('support/assertions.js', false));
        $this->status('Created', $this->cypressPath('support/index.d.ts', false));

        $this->createCypressConfig();

        if (! $this->files->exists($path = base_path('.env.cypress'))) {
            $this->files->copy(base_path('.env'), $path);

            $this->status('Created', '.env.cypress');
        }

        $this->lineBreak();
    }

    /**
     * Set the initial cypress.json configuration for the project.
     */
    protected function createCypressConfig(): void
    {
        match (true) {
            str_starts_with($this->cypressVersion(), '^10') => $this->cypressConfig(),
            default => $this->cypressOlderConfig()
        };
    }

    /**
     * For Cypress 10
     * Set the initial cypress.config.js configuration for the project.
     */
    protected function cypressConfig()
    {
        if (! $this->option('force') && $this->files->exists($this->cypressConfigPath())) {
            $this->warn('Existing Cypress configuration file found');
            $overwrite = $this->confirm('Do you want to overwrite the existing configuration?', 'y');
            if(! $overwrite) {
                $this->info('Please upgrade the file manually.');
                return;
            }
        }

        $this->files->put(
            $this->cypressConfigPath(),
            str_replace(
                ['%baseUrl%', '%cypressPath%'],
                [config('app.url'), $this->cypressPath('', false)],
                $this->files->get(__DIR__ . '/stubs/cypress.config.js')
            )
        );

        $this->status('Created', $this->cypressConfigPath(false));
    }

    /**
     * For Cypress version below 10
     * Set the initial cypress.json configuration for the project.
     */
    protected function cypressOlderConfig()
    {
        $config = [];
        $configExists = $this->files->exists($this->cypressConfigPath());

        if ($configExists) {
            $config = json_decode($this->files->get($this->cypressConfigPath()), true);
        }

        $this->files->put(
            $this->cypressConfigPath(),
            json_encode($this->mergeCypressConfig($config), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        $this->status($configExists ? 'Updated' : 'Created', $this->cypressConfigPath(false));
    }

    /**
     * Merge the user's current cypress.json config with this package's recommended defaults.
     */
    protected function mergeCypressConfig(array $config = []): array
    {
        return array_merge([
            'baseUrl' => config('app.url'),
            'chromeWebSecurity' => false,
            'retries' => 2,
            'defaultCommandTimeout' => 5000,
            'watchForFileChanges' => true,
            "integrationFolder" => $this->cypressPath('integration', false),
            "pluginsFile" => $this->cypressPath('plugins/index.js', false),
            "videosFolder" => $this->cypressPath('videos', false),
            "supportFile" => $this->cypressPath('support/index.js', false),
            "screenshotsFolder" => $this->cypressPath('screenshots', false),
            "fixturesFolder" => $this->cypressPath('fixture', false)
        ], $config);
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

npm install cypress --save-dev && npx cypress open

EOT
        );
    }

    /**
     * Check whether cypress is installed or not from pcakage.json.
     */
    protected function isCypressInstalled(): bool
    {
        return Arr::get($this->getPackageJson(), 'devDependencies.cypress') || Arr::get($this->getPackageJson(), 'dependencies.cypress');
    }

    /**
     * Get the package.json file from the consuming app.
     */
    protected function getPackageJson(): array
    {
        return $this->files->exists(base_path('pcakage.json')) ? json_decode(base_path('package.json'), true) : [];
    }

    /**
     * Determine the installed version for cypress.
     */
    protected function cypressVersion(): ?string
    {
        return Arr::get($this->getPackageJson(), 'devDependencies.cypress', Arr::get($this->getPackageJson(), 'dependencies.cypress'), null);
    }
}
