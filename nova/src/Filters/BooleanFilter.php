<?php

namespace Laravel\Nova\Filters;

use Illuminate\Http\Request;
use Illuminate\Container\Container;

abstract class BooleanFilter extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'boolean-filter';

    /**
     * Set the default options for the filter.
     *
     * @return array
     */
    public function default()
    {
        $container = Container::getInstance();

        return collect($this->options($container->make(Request::class)))->values()->mapWithKeys(function ($option) {
            return [$option => false];
        })->all();
    }
}
