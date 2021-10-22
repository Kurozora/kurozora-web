<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Text;
use Titasgailius\SearchRelations\SearchesRelations;

class MediaRelation extends Resource
{
    use SearchesRelations;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\MediaRelation::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\MediaRelation|null
     */
    public $resource;

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
     * The relationship columns that should be searched.
     *
     * @var array
     */
    public static $searchRelations = [
        'model' => ['original_title'],
        'related' => ['original_title'],
        'relation' => ['name'],
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Relations';

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

            MorphTo::make('Media', 'model')
                ->types([
                    Anime::class,
                ])
                ->searchable()
                ->sortable(),

            Text::make('Model Type')
                ->onlyOnIndex()
                ->onlyOnDetail()
                ->sortable(),

            BelongsTo::make('Relation')
                ->sortable(),

            MorphTo::make('Related Media', 'related')
                ->types([
                    Anime::class,
                ])
                ->searchable()
                ->sortable(),

            Text::make('Related Type', 'related_type')
                ->onlyOnIndex()
                ->onlyOnDetail()
                ->sortable(),
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

    /**
     * The icon of the resource.
     *
     * @var string
     */
    public static string $icon = '
        <svg class="sidebar-icon" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <path fill="var(--sidebar-icon)" d="M68.0049196,7 C80.3768091,7 90.3380199,13.2361017 92.9650012,19.2281804 L93.0612245,19.4555973 L93.0612245,32 L84.897,32 L84.8979592,21.3840974 C83.8848739,18.5702501 77.3006841,13.5306122 66.9599535,13.5306122 C56.7758154,13.5306122 50.0714205,18.4594852 49.0239137,21.2565419 L48.9795918,21.3840974 L48.979,32 L42.448,32 L42.4489796,21.3840974 C41.5626207,18.6123095 34.8516547,13.5306122 24.4687045,13.5306122 C14.3285598,13.5306122 7.74836805,18.4163772 6.58134467,21.2526735 L6.53061224,21.3840974 L6.53061224,78.8367347 C10.8779545,75.434934 17.4201026,73.5870409 24.4687045,73.5870409 C24.9823601,73.5870409 25.4933257,73.5968542 26.0009643,73.6163021 L26.0009985,80.2823904 C25.6902324,80.2745901 25.3754412,80.270598 25.0566481,80.270598 C18.6227204,80.270598 14.2552246,81.954816 11.2505486,83.8416713 L10.7873489,84.1410638 C10.4106159,84.3916008 10.0568364,84.6442961 9.72440733,84.8957745 L9.33563817,85.1967485 L8.96675003,85.4952583 C7.75164092,86.4148331 6.66216175,87 4.69284403,87 C2.28413623,87 0.114049824,85.7301177 0.0043469757,82.4945993 L0,82.2350672 L0,19.4555973 C2.47209485,13.3949769 12.5283241,7 25.0566481,7 C34.5681377,7 43.0739873,10.8453579 46.5517222,15.1087044 C50.0295429,10.8453579 58.5353925,7 68.0049196,7 Z M88.3557839,87.9509676 C89.3029742,87.9509676 90.0708235,88.7044773 90.0708235,89.6339784 L90.0708235,89.6339784 L90.0708235,91.3169893 C90.0708235,92.2464903 89.3029742,93 88.3557839,93 L88.3557839,93 L43.7647549,93 C42.8175646,93 42.0497153,92.2464903 42.0497153,91.3169893 L42.0497153,91.3169893 L42.0497153,89.6339784 C42.0497153,88.7044773 42.8175646,87.9509676 43.7647549,87.9509676 L43.7647549,87.9509676 L88.3557839,87.9509676 Z M94.8684211,38.5425806 C97.7025138,38.5425806 100,40.8341606 100,43.6609677 L100,43.6609677 L100,77.7835483 C100,80.6103554 97.7025138,82.9019354 94.8684211,82.9019354 L94.8684211,82.9019354 L36.7105263,82.9019354 C33.8764336,82.9019354 31.5789474,80.6103554 31.5789474,77.7835483 L31.5789474,77.7835483 L31.5789474,43.6609677 C31.5789474,40.8341606 33.8764336,38.5425806 36.7105263,38.5425806 L36.7105263,38.5425806 L94.8684211,38.5425806 Z M93.108162,44.6063608 L38.8318462,44.6063608 L38.8318462,76.47751 L93.108162,76.47751 L93.108162,44.6063608 Z"/>
        </svg>
    ';
}
