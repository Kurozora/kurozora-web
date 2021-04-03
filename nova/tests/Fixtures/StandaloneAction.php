<?php

namespace Laravel\Nova\Tests\Fixtures;

use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;

class StandaloneAction extends Action
{
    use ProvidesActionFields;

    public static $appliedFields = [];

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return string|void
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        static::$appliedFields[] = $fields;

        return Action::message('Hello World');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Text::make('Name')->rules('required'),
            Text::make('Email')->rules('required', 'email'),
        ];
    }
}
