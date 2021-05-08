<?php

namespace App\Nova;

use App\Enums\AnimeImageType;
use App\Nova\Actions\GenerateColorsFromImage;
use Chaseconey\ExternalImage\ExternalImage;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Timothyasp\Color\Color;

class AnimeImage extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = 'App\Models\AnimeImages';

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
    public static $group = 'Anime';

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

            BelongsTo::make('Anime')
                ->searchable()
                ->sortable()
                ->required(),

            ExternalImage::make('URL', 'url')
                ->height(200)
                ->radius(5)
                ->rules('required')
                ->help('The URL to request the image asset.'),

            Select::make('Type')
                ->options(AnimeImageType::asSelectArray())
                ->displayUsingLabels()
                ->rules('required')
                ->help('The type of the image asset.'),

            Heading::make('Colors (automatically generated if empty)'),

            Color::make('Background Color')
                ->help('The average background color of the image.'),

            Color::make('Text Color 1')
                ->help('The primary text color that may be used if the background color is displayed.'),

            Color::make('Text Color 2')
                ->help('The secondary text color that may be used if the background color is displayed.'),

            Color::make('Text Color 3')
                ->help('The tertiary text color that may be used if the background color is displayed.'),

            Color::make('Text Color 4')
                ->help('The final post-tertiary text color that may be used if the background color is displayed.'),

            Heading::make('Dimensions (automatically generated if empty)'),

            Number::make('Width')
                ->help('The maximum width available for the image.'),

            Number::make('Height')
                ->help('The maximum height available for the image.'),
        ];
    }

    /**
     * Returns the user-friendly display name of the resource.
     *
     * @return string
     */
    public static function label(): string
    {
        return 'Images';
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
        return [
            new GenerateColorsFromImage()
        ];
    }

    /**
     * The icon of the resource.
     *
     * @var string
     */
    public static string $icon = '
        <svg class="sidebar-icon" viewBox="0 0 130 106" xmlns="http://www.w3.org/2000/svg">
            <path fill="var(--sidebar-icon)" d="M15.6232,82.45703 L24.6564,82.45703 L24.6564,90.85547 C24.6564,100.9141 29.7834,105.9922 39.9884,105.9922 L114.061,105.9922 C124.217,105.9922 129.393,100.9141 129.393,90.85547 L129.393,38.9512 C129.393,28.9414 124.217,23.8633 114.061,23.8633 L104.979,23.8633 L104.979,15.9531 C104.979,5.8945 99.852,0.8164 89.647,0.8164 L15.6232,0.8164 C5.3693,0.8164 0.2912,5.8945 0.2912,15.9531 L0.2912,67.3691 C0.2912,77.4277 5.3693,82.45703 15.6232,82.45703 Z M15.7209,74.5957 C10.838,74.5957 8.1525,72.0078 8.1525,66.9297 L8.1525,16.3926 C8.1525,11.3145 10.838,8.6777 15.7209,8.6777 L89.549,8.6777 C94.334,8.6777 97.117,11.3145 97.117,16.3926 L97.117,23.8633 L39.9884,23.8633 C29.7834,23.8633 24.6564,28.8926 24.6564,38.9512 L24.6564,74.5957 L15.7209,74.5957 Z M32.5177,39.3906 C32.5177,34.3125 35.2521,31.7246 40.1349,31.7246 L113.914,31.7246 C118.748,31.7246 121.531,34.3125 121.531,39.3906 L121.531,80.16211 L103.611,63.2676 C101.463,61.2656 98.924,60.2891 96.238,60.2891 C93.553,60.2891 91.209,61.2168 88.914,63.2188 L66.9904,82.65234 L58.2502,74.7422 C56.1505,72.9355 53.9533,71.959 51.6095,71.959 C49.4611,71.959 47.4103,72.8379 45.3595,74.6934 L32.5177,85.82617 L32.5177,39.3906 Z M59.0314,64.4883 C65.3302,64.4883 70.506,59.3125 70.506,52.916 C70.506,46.666 65.3302,41.3926 59.0314,41.3926 C52.6838,41.3926 47.508,46.666 47.508,52.916 C47.508,59.3125 52.6838,64.4883 59.0314,64.4883 Z" fill-rule="nonzero"></path>
        </svg>
    ';
}
