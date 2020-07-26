<?php

namespace Laravel\Nova\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Laravel\Nova\Console\Concerns\AcceptsNameAndVendor;
use Symfony\Component\Process\Process;

class FieldCommand extends Command
{
    use AcceptsNameAndVendor;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nova:field {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new field';

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
            __DIR__.'/field-stubs',
            $this->fieldPath()
        );

        // Field.js replacements...
        $this->replace('{{ component }}', $this->fieldName(), $this->fieldPath().'/resources/js/field.js');

        // Field.php replacements...
        $this->replace('{{ namespace }}', $this->fieldNamespace(), $this->fieldPath().'/src/Field.stub');
        $this->replace('{{ class }}', $this->fieldClass(), $this->fieldPath().'/src/Field.stub');
        $this->replace('{{ component }}', $this->fieldName(), $this->fieldPath().'/src/Field.stub');

        (new Filesystem)->move(
            $this->fieldPath().'/src/Field.stub',
            $this->fieldPath().'/src/'.$this->fieldClass().'.php'
        );

        // FieldServiceProvider.php replacements...
        $this->replace('{{ namespace }}', $this->fieldNamespace(), $this->fieldPath().'/src/FieldServiceProvider.stub');
        $this->replace('{{ component }}', $this->fieldName(), $this->fieldPath().'/src/FieldServiceProvider.stub');

        (new Filesystem)->move(
            $this->fieldPath().'/src/FieldServiceProvider.stub',
            $this->fieldPath().'/src/FieldServiceProvider.php'
        );

        // Field composer.json replacements...
        $this->replace('{{ name }}', $this->argument('name'), $this->fieldPath().'/composer.json');
        $this->replace('{{ escapedNamespace }}', $this->escapedFieldNamespace(), $this->fieldPath().'/composer.json');

        // Register the field...
        $this->addFieldRepositoryToRootComposer();
        $this->addFieldPackageToRootComposer();
        $this->addScriptsToNpmPackage();

        if ($this->confirm("Would you like to install the field's NPM dependencies?", true)) {
            $this->installNpmDependencies();

            $this->output->newLine();
        }

        if ($this->confirm("Would you like to compile the field's assets?", true)) {
            $this->compile();

            $this->output->newLine();
        }

        if ($this->confirm('Would you like to update your Composer packages?', true)) {
            $this->composerUpdate();
        }
    }

    /**
     * Add a path repository for the field to the application's composer.json file.
     *
     * @return void
     */
    protected function addFieldRepositoryToRootComposer()
    {
        $composer = json_decode(file_get_contents(base_path('composer.json')), true);

        $composer['repositories'][] = [
            'type' => 'path',
            'url' => './'.$this->relativeFieldPath(),
        ];

        file_put_contents(
            base_path('composer.json'),
            json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    /**
     * Add a package entry for the field to the application's composer.json file.
     *
     * @return void
     */
    protected function addFieldPackageToRootComposer()
    {
        $composer = json_decode(file_get_contents(base_path('composer.json')), true);

        $composer['require'][$this->argument('name')] = '*';

        file_put_contents(
            base_path('composer.json'),
            json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    /**
     * Add a path repository for the field to the application's composer.json file.
     *
     * @return void
     */
    protected function addScriptsToNpmPackage()
    {
        $package = json_decode(file_get_contents(base_path('package.json')), true);

        $package['scripts']['build-'.$this->fieldName()] = 'cd '.$this->relativeFieldPath().' && npm run dev';
        $package['scripts']['build-'.$this->fieldName().'-prod'] = 'cd '.$this->relativeFieldPath().' && npm run prod';

        file_put_contents(
            base_path('package.json'),
            json_encode($package, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    /**
     * Install the field's NPM dependencies.
     *
     * @return void
     */
    protected function installNpmDependencies()
    {
        $this->executeCommand('npm set progress=false && npm install', $this->fieldPath());
    }

    /**
     * Compile the field's assets.
     *
     * @return void
     */
    protected function compile()
    {
        $this->executeCommand('npm run dev', $this->fieldPath());
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
     * Get the path to the tool.
     *
     * @return string
     */
    protected function fieldPath()
    {
        return base_path('nova-components/'.$this->fieldClass());
    }

    /**
     * Get the relative path to the field.
     *
     * @return string
     */
    protected function relativeFieldPath()
    {
        return 'nova-components/'.$this->fieldClass();
    }

    /**
     * Get the field's namespace.
     *
     * @return string
     */
    protected function fieldNamespace()
    {
        return Str::studly($this->fieldVendor()).'\\'.$this->fieldClass();
    }

    /**
     * Get the field's escaped namespace.
     *
     * @return string
     */
    protected function escapedFieldNamespace()
    {
        return str_replace('\\', '\\\\', $this->fieldNamespace());
    }

    /**
     * Get the field's class name.
     *
     * @return string
     */
    protected function fieldClass()
    {
        return Str::studly($this->fieldName());
    }

    /**
     * Get the field's vendor.
     *
     * @return string
     */
    protected function fieldVendor()
    {
        return explode('/', $this->argument('name'))[0];
    }

    /**
     * Get the field's base name.
     *
     * @return string
     */
    protected function fieldName()
    {
        return explode('/', $this->argument('name'))[1];
    }
}
