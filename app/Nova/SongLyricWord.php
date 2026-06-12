<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;

class SongLyricWord extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\SongLyricWord::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\SongLyricWord|null
     */
    public $resource;

    /**
     * The single value that should be used to represent the resource.
     *
     * @var string
     */
    public static $title = 'text';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'text'
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Song';

    /**
     * Indicates if the resource should be displayed in the sidebar.
     *
     * @var bool
     */
    public static $displayInNavigation = false;

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     *
     * @return array
     */
    public function fields(Request $request): array
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make('Line', 'line', SongLyricLine::class),

            Number::make('Position')
                ->sortable(),

            Number::make('Begin (ms)', 'begin_ms'),

            Number::make('End (ms)', 'end_ms'),

            Text::make('Text'),

            Boolean::make('Background', 'is_background'),

            Boolean::make('Trailing space', 'trailing_space'),
        ];
    }
}
