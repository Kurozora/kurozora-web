<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;

class ForumThread extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\ForumThread';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'title'
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Forum';

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make('User')
                ->sortable()
                ->searchable(),

            BelongsTo::make('Forum Section')
                ->sortable()
                ->searchable(),

            Text::make('Title')
                ->rules('required', 'max:255')
                ->sortable(),

            Textarea::make('Content')
                ->rules('required')
                ->hideFromIndex(),

            Text::make('Posted from IP address', 'ip')
                ->rules('required', 'max:255')
                ->sortable()
                ->hideFromIndex(),

            Boolean::make('Thread is locked', 'locked')
                ->sortable()
        ];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title()
    {
        return 'Title: "' . $this->title . '" (ID: ' . $this->id . ')';
    }

    /**
     * Returns the user-friendly display name of the resource.
     *
     * @return string
     */
    public static function label() {
        return 'Forum Threads';
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }

    /**
     * Determine if the given resource is authorizable.
     *
     * @return bool
     */
    public static function authorizable()
    {
        return false;
    }

    /**
     * The icon of the resource.
     *
     * @var string
     */
    public static $icon = '
        <svg class="sidebar-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path fill="var(--sidebar-icon)" d="M2 15V5c0-1.1.9-2 2-2h16a2 2 0 0 1 2 2v15a1 1 0 0 1-1.7.7L16.58 17H4a2 2 0 0 1-2-2zM20 5H4v10h13a1 1 0 0 1 .7.3l2.3 2.29V5z"/>
        </svg>
    ';
}
