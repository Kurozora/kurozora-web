<?php

namespace Laravel\Nova\Tests\Fixtures;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Resource;

class CallableDefaultResource extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \Laravel\Nova\Tests\Fixtures\CallableDefault::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make('ID', 'id'),

            Select::make('Align')
                ->default('end') // This value is_callable because `end` is a valid function
                ->options([
                    'start' => 'Start',
                    'end' => 'End',
                ]),
        ];
    }

    /**
     * Get the URI key for the resource.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'callable-defaults';
    }
}
