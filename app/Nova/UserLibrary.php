<?php

namespace App\Nova;

use App\Enums\UserLibraryStatus;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Select;

class UserLibrary extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\UserLibrary::class;

    /**
     * Determine if the resource should be available for the given request.
     *
     * @param Request $request
     * @return bool
     */
    public static function authorizedToViewAny(Request $request): bool
    {
        return $request->user()?->can('viewUserLibrary') ?? false;
    }

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Users';

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request): array
    {
        return [
            Heading::make('Identification')
                ->onlyOnDetail(),

            ID::make()->sortable(),

            Heading::make('Meta information'),

            MorphTo::make('Trackable')
                ->types([
                    Anime::class,
                    Game::class,
                    Manga::class,
                ])
                ->sortable()
                ->searchable(),

            BelongsTo::make('User')
                ->sortable()
                ->searchable(),

            Select::make('Status')
                ->options(function () {
                    return match ($this->trackable_type) {
                        \App\Models\Anime::class => UserLibraryStatus::asAnimeSelectArray(),
                        \App\Models\Manga::class => UserLibraryStatus::asMangaSelectArray(),
                        \App\Models\Game::class => UserLibraryStatus::asGameSelectArray(),
                        default => UserLibraryStatus::asSelectArray()
                    };
                })
                ->displayUsingLabels()
                ->required()
                ->sortable()
                ->help('The status of the anime in the library. For example: Watching'),

            Date::make('Start Date')
                ->default(now())
                ->displayUsing(function ($startDate) {
                    return $startDate?->format('Y-m-d');
                })
                ->required()
                ->sortable()
                ->help('The date on which the user started tracking. For example: 2015-12-03'),

            Date::make('End Date')
                ->displayUsing(function ($startDate) {
                    return $startDate?->format('Y-m-d');
                })
                ->sortable()
                ->help('The date on which the user finished tracking. For example: 2015-12-03'),
        ];
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
}
