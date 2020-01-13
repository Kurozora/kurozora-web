<?php

namespace Laravel\Nova\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class ResourceCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nova:resource';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new resource class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Resource';

    /**
     * A list of resource names which are protected.
     *
     * @var array
     */
    protected $protectedNames = [
        'card',
        'cards',
        'dashboard',
        'dashboards',
        'metric',
        'metrics',
        'script',
        'scripts',
        'search',
        'searches',
        'style',
        'styles',
    ];

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        parent::handle();

        $this->callSilent('nova:base-resource', [
            'name' => 'Resource',
        ]);
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $model = $this->option('model');

        if (is_null($model)) {
            $model = $this->laravel->getNamespace().$this->argument('name');
        } elseif (! Str::startsWith($model, [
            $this->laravel->getNamespace(), '\\',
        ])) {
            $model = $this->laravel->getNamespace().$model;
        }

        $resourceName = $this->argument('name');

        if (in_array(strtolower($resourceName), $this->protectedNames)) {
            $this->warn("You *must* override the uriKey method for your {$resourceName} resource.");
        }

        return str_replace(
            'DummyFullModel', $model, parent::buildClass($name)
        );
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/resource.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Nova';
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['model', 'm', InputOption::VALUE_REQUIRED, 'The model class being represented.'],
        ];
    }
}
