<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;

class Source extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Source::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\Source|null
     */
    public $resource;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name',
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Media';

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

            Text::make('Name')
                ->sortable()
                ->help('The name of the source.')
                ->rules('unique:' . \App\Models\Source::TABLE_NAME . ',name')
                ->required(),

            Text::make('Description')
                ->sortable()
                ->help('An explanation of what the source means.')
                ->required(),

            HasMany::make('Anime'),
        ];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        $source = $this->resource;

        return $source->name . ' (ID: ' . $source->id . ')';
    }

    /**
     * Determine if this resource is available for navigation.
     *
     * @param Request $request
     * @return bool
     */
    public static function availableForNavigation(Request $request): bool
    {
        return $request->user()->can('viewSource');
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
     * The icon of the resource.
     *
     * @var string
     */
    public static string $icon = '
        <svg class="sidebar-icon" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <path fill="var(--sidebar-icon)" d="M49.9999699,100 C77.3529105,100 100.000371,77.3039091 100.000371,50 C100.000371,22.6470594 77.3039191,0 49.9509785,0 C22.6470293,0 0,22.6470594 0,50 C0,77.3039091 22.696121,100 49.9999699,100 Z M49.9999699,91.6666499 C26.8626957,91.6666499 8.38231131,73.1372742 8.38231131,50 C8.38231131,26.8627258 26.8137043,8.33335007 49.9509785,8.33335007 C73.0882527,8.33335007 91.6176285,26.8627258 91.6669181,50 C91.7160127,73.1372742 73.1372441,91.6666499 49.9999699,91.6666499 Z M49.9999699,81.0784123 C67.0098103,81.0784123 81.0784625,66.9607487 81.0784625,50 C81.0784625,32.9901596 66.960819,18.872516 49.9999699,18.872516 C32.9902298,18.872516 18.9215777,32.9901596 18.9215777,50 C18.9215777,66.9607487 33.0392212,81.0784123 49.9999699,81.0784123 Z M49.9999699,73.1862656 C37.4019621,73.1862656 26.8137043,62.5980078 26.8137043,50 C26.8137043,37.3039091 37.3529707,26.7647431 49.9999699,26.7647431 C62.5980781,26.7647431 73.1862354,37.3529004 73.1862354,50 C73.1862354,62.6470996 62.6470695,73.1862656 49.9999699,73.1862656 Z M50.0490616,62.6960909 C57.0097501,62.6960909 62.7450522,57.0097802 62.7450522,49.9510086 C62.7450522,42.9902198 57.0097501,37.3039091 50.0490616,37.3039091 C43.039181,37.3039091 37.303879,42.9902198 37.303879,49.9510086 C37.303879,57.0097802 43.039181,62.6960909 50.0490616,62.6960909 Z"/>
        </svg>
    ';
}
