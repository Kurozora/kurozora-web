<?php

namespace Laravel\Nova\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Laravel\Nova\Console\Concerns\AcceptsNameAndVendor;
use Symfony\Component\Process\Process;

class AssetCommand extends Command
{
    use AcceptsNameAndVendor, RenamesStubs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nova:asset {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new asset';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (! $this->hasValidNameArgument()) {
            return;
        }

        (new Filesystem)->copyDirectory(
            __DIR__.'/asset-stubs',
            $this->assetPath()
        );

        // AssetServiceProvider.php replacements...
        $this->replace('{{ namespace }}', $this->assetNamespace(), $this->assetPath().'/src/AssetServiceProvider.stub');
        $this->replace('{{ component }}', $this->assetName(), $this->assetPath().'/src/AssetServiceProvider.stub');
        $this->replace('{{ name }}', $this->assetName(), $this->assetPath().'/src/AssetServiceProvider.stub');

        // Asset composer.json replacements...
        $this->replace('{{ name }}', $this->argument('name'), $this->assetPath().'/composer.json');
        $this->replace('{{ escapedNamespace }}', $this->escapedAssetNamespace(), $this->assetPath().'/composer.json');

        // Rename the stubs with the proper file extensions...
        $this->renameStubs();

        // Register the asset...
        $this->addAssetRepositoryToRootComposer();
        $this->addAssetPackageToRootComposer();
        $this->addScriptsToNpmPackage();

        if ($this->confirm("Would you like to install the asset's NPM dependencies?", true)) {
            $this->installNpmDependencies();

            $this->output->newLine();
        }

        if ($this->confirm("Would you like to compile the asset's assets?", true)) {
            $this->compile();

            $this->output->newLine();
        }

        if ($this->confirm('Would you like to update your Composer packages?', true)) {
            $this->composerUpdate();
        }
    }

    /**
     * Get the array of stubs that need PHP file extensions.
     *
     * @return array
     */
    protected function stubsToRename()
    {
        return [
            $this->assetPath().'/src/AssetServiceProvider.stub',
        ];
    }

    /**
     * Add a path repository for the asset to the application's composer.json file.
     *
     * @return void
     */
    protected function addAssetRepositoryToRootComposer()
    {
        $composer = json_decode(file_get_contents(base_path('composer.json')), true);

        $composer['repositories'][] = [
            'type' => 'path',
            'url' => './'.$this->relativeAssetPath(),
        ];

        file_put_contents(
            base_path('composer.json'),
            json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    /**
     * Add a package entry for the asset to the application's composer.json file.
     *
     * @return void
     */
    protected function addAssetPackageToRootComposer()
    {
        $composer = json_decode(file_get_contents(base_path('composer.json')), true);

        $composer['require'][$this->argument('name')] = '*';

        file_put_contents(
            base_path('composer.json'),
            json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * Add a path repository for the asset to the application's composer.json file.
     *
     * @return void
     */
    protected function addScriptsToNpmPackage()
    {
        $package = json_decode(file_get_contents(base_path('package.json')), true);

        $package['scripts']['build-'.$this->assetName()] = 'cd '.$this->relativeAssetPath().' && npm run dev';
        $package['scripts']['build-'.$this->assetName().'-prod'] = 'cd '.$this->relativeAssetPath().' && npm run prod';

        file_put_contents(
            base_path('package.json'),
            json_encode($package, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    /**
     * Install the asset's NPM dependencies.
     *
     * @return void
     */
    protected function installNpmDependencies()
    {
        $this->executeCommand('npm set progress=false && npm install', $this->assetPath());
    }

    /**
     * Compile the asset's assets.
     *
     * @return void
     */
    protected function compile()
    {
        $this->executeCommand('npm run dev', $this->assetPath());
    }

    /**
     * Update the project's composer dependencies.
     *
     * @return void
     */
    protected function composerUpdate()
    {
        $this->executeCommand('composer update', getcwd());
    }

    /**
     * Run the given command as a process.
     *
     * @param  string  $command
     * @param  string  $path
     * @return void
     */
    protected function executeCommand($command, $path)
    {
        $process = (Process::fromShellCommandline($command, $path))->setTimeout(null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            $process->setTty(true);
        }

        $process->run(function ($type, $line) {
            $this->output->write($line);
        });
    }

    /**
     * Replace the given string in the given file.
     *
     * @param  string  $search
     * @param  string  $replace
     * @param  string  $path
     * @return void
     */
    protected function replace($search, $replace, $path)
    {
        file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
    }

    /**
     * Get the path to the asset.
     *
     * @return string
     */
    protected function assetPath()
    {
        return base_path('nova-components/'.$this->assetClass());
    }

    /**
     * Get the relative path to the asset.
     *
     * @return string
     */
    protected function relativeAssetPath()
    {
        return 'nova-components/'.$this->assetClass();
    }

    /**
     * Get the asset's namespace.
     *
     * @return string
     */
    protected function assetNamespace()
    {
        return Str::studly($this->assetVendor()).'\\'.$this->assetClass();
    }

    /**
     * Get the asset's escaped namespace.
     *
     * @return string
     */
    protected function escapedAssetNamespace()
    {
        return str_replace('\\', '\\\\', $this->assetNamespace());
    }

    /**
     * Get the asset's class name.
     *
     * @return string
     */
    protected function assetClass()
    {
        return Str::studly($this->assetName());
    }

    /**
     * Get the asset's vendor.
     *
     * @return string
     */
    protected function assetVendor()
    {
        return explode('/', $this->argument('name'))[0];
    }

    /**
     * Get the asset's base name.
     *
     * @return string
     */
    protected function assetName()
    {
        return explode('/', $this->argument('name'))[1];
    }
}
