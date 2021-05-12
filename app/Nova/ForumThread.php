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
    public static string $model = 'App\Models\ForumThread';

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
     * @param Request $request
     * @return array
     */
    public function fields(Request $request): array
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

            Text::make('Posted from IP address', 'ip_address')
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
    public function title(): string
    {
        return 'Title: "' . $this->title . '" (ID: ' . $this->id . ')';
    }

    /**
     * Returns the user-friendly display name of the resource.
     *
     * @return string
     */
    public static function label(): string
    {
        return 'Forum Threads';
    }

    /**
     * Get the cards available for the request.
     *
     * @param Request $request
     * @return array
     */
    public function cards(Request $request): array
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function filters(Request $request): array
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function lenses(Request $request): array
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function actions(Request $request): array
    {
        return [];
    }

    /**
     * Determine if the given resource is authorizable.
     *
     * @return bool
     */
    public static function authorizable(): bool
    {
        return false;
    }

    /**
     * The icon of the resource.
     *
     * @var string
     */
    public static string $icon = '
        <svg class="sidebar-icon" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <path fill="var(--sidebar-icon)" d="M0,65 L0,15 C0,9.5 4.5,5 10,5 L90,5 C95.5228475,5 100,9.4771525 100,15 L100,90 C99.9718997,92.0003758 98.7541285,93.791442 96.9042423,94.5531598 C95.0543561,95.3148777 92.9285015,94.9005986 91.5,93.5 L72.9,75 L10,75 C4.4771525,75 0,70.5228475 0,65 Z M90,15 L10,15 L10,65 L75,65 C76.3188463,65.0187852 77.5768477,65.5579287 78.5,66.5 L90,77.95 L90,15 Z"/>
        </svg>
    ';
}
