<?php

namespace Laravel\Nova\Fields;

use Laravel\Nova\Http\Requests\NovaRequest;

class Stack extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'stack-field';

    /**
     * Indicates if the element should be shown on the creation view.
     *
     * @var \Closure|bool
     */
    public $showOnCreation = false;

    /**
     * Indicates if the element should be shown on the update view.
     *
     * @var \Closure|bool
     */
    public $showOnUpdate = false;

    /**
     * The contents of the Stack field.
     *
     * @var array
     */
    public $lines;

    /**
     * Create a new Stack field.
     *
     * @param  string  $name
     * @param  string|array|null $attribute
     * @param  array $lines
     * @return void
     */
    public function __construct($name, $attribute = null, $lines = [])
    {
        if (is_array($attribute)) {
            $lines = $attribute;
            $attribute = null;
        }

        parent::__construct($name, $attribute);

        $this->lines = $lines;
    }

    /**
     * Resolve the field's value for display.
     *
     * @param  mixed  $resource
     * @param  string|null  $attribute
     * @return void
     */
    public function resolveForDisplay($resource, $attribute = null)
    {
        $this->prepareLines($resource, $attribute);
    }

    /**
     * Prepare the stack for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), [
            'lines' => $this->lines->all(),
        ]);
    }

    /**
     * Prepare each line for serialization.
     *
     * @param  mixed  $resource
     * @param string $attribute
     * @return void
     */
    public function prepareLines($resource, $attribute = null)
    {
        $this->ensureLinesAreResolveable();

        $request = app(NovaRequest::class);

        $this->lines = $this->lines->filter(function ($field) use ($request, $resource) {
            if ($request->isResourceIndexRequest()) {
                return $field->isShownOnIndex($request, $resource);
            }

            return $field->isShownOnDetail($request, $resource);
        })->values()->each->resolveForDisplay($resource, $attribute);
    }

    /**
     * Ensure that each line for the field is resolvable.
     *
     * @return void
     */
    protected function ensureLinesAreResolveable()
    {
        $this->lines = collect($this->lines)->map(function ($line) {
            if (is_callable($line)) {
                return Line::make('Anonymous', $line);
            }

            return $line;
        });
    }
}
