<?php

namespace Laravel\Nova\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class ResourceCommand extends GeneratorCommand
{
    use ResolvesStubPath;

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
        $resourceName = $this->argument('name');
        $model = $this->option('model');

        $modelNamespace = $this->getModelNamespace();

        if (is_null($model)) {
            $model = $modelNamespace.str_replace('/', '\\', $resourceName);
        } elseif (! Str::startsWith($model, [
            $modelNamespace, '\\',
        ])) {
            $model = $modelNamespace.$model;
        }

        if (in_array(strtolower($resourceName), $this->protectedNames)) {
            $this->warn("You *must* override the uriKey method for your {$resourceName} resource.");
        }

        $replace = [
            'DummyFullModel' => $model,
            '{{ namespacedModel }}' => $model,
            '{{namespacedModel}}' => $model,
        ];

        $result = str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );

        $baseResourceClass = $this->getBaseResourceClass();

        if (! class_exists($baseResourceClass)) {
            $baseResourceClass = 'Laravel\Nova\Resource';
        } elseif (! Str::contains($resourceName, '/') && class_exists($baseResourceClass)) {
            return $result;
        }

        $lineEndingCount = [
            "\r\n" => substr_count($result, "\r\n"),
            "\r" => substr_count($result, "\r"),
            "\n" => substr_count($result, "\n"),
        ];

        $eol = array_keys($lineEndingCount, max($lineEndingCount))[0];

        return str_replace(
            'use Laravel\Nova\Http\Requests\NovaRequest;'.$eol,
            'use Laravel\Nova\Http\Requests\NovaRequest;'.$eol."use {$baseResourceClass};".$eol,
            $result
        );
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->resolveStubPath('/stubs/nova/resource.stub');
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
     * Get the base resource class.
     *
     * @return class-string
     */
    protected function getBaseResourceClass()
    {
        $rootNamespace = $this->laravel->getNamespace();

        return "{$rootNamespace}Nova\Resource";
    }

    /**
     * Get the default namespace for the class.
     *
     * @return string
     */
    protected function getModelNamespace()
    {
        $rootNamespace = $this->laravel->getNamespace();

        return is_dir(app_path('Models')) ? $rootNamespace.'Models\\' : $rootNamespace;
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
