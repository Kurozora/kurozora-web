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
        <svg class="sidebar-icon" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <path fill="var(--sidebar-icon)" d="M11.8758995,72.2374064 L18.8728585,72.2374064 L18.8728585,78.7426914 C18.8728585,86.5339306 22.8441431,90.4673382 30.748758,90.4673382 L88.1241005,90.4673382 C95.9907608,90.4673382 100,86.5339306 100,78.7426914 L100,38.5385502 C100,30.7851339 95.9907608,26.8517263 88.1241005,26.8517263 L81.0893419,26.8517263 L81.0893419,20.7246235 C81.0893419,12.9334076 77.1180572,9 69.2134424,9 L11.8758995,9 C3.93340759,9 0,12.9334076 0,20.7246235 L0,60.5505593 C0,68.3417753 3.93340759,72.2374064 11.8758995,72.2374064 Z M11.9515762,66.1481575 C8.16936712,66.1481575 6.08922571,64.1436153 6.08922571,60.2102078 L6.08922571,21.0650525 C6.08922571,17.1316449 8.16936712,15.0892257 11.9515762,15.0892257 L69.1375333,15.0892257 C72.8439108,15.0892257 74.999574,17.1316449 74.999574,21.0650525 L74.999574,26.8517263 L30.748758,26.8517263 C22.8441431,26.8517263 18.8728585,30.7473343 18.8728585,38.5385502 L18.8728585,66.1481575 L11.9515762,66.1481575 Z M24.9620842,38.8789018 C24.9620842,34.9454942 27.0801027,32.940952 30.8622343,32.940952 L88.0102369,32.940952 C91.7545689,32.940952 93.9102321,34.9454942 93.9102321,38.8789018 L93.9102321,70.4598015 L80.029713,57.373609 C78.3659097,55.8228948 76.3992446,55.066515 74.3187159,55.066515 C72.2389618,55.066515 70.4233403,55.7850952 68.6456734,57.3358094 L51.6640357,72.3886902 L44.8940294,66.2616338 C43.2676384,64.8621956 41.5657257,64.1058157 39.7502591,64.1058157 C38.086146,64.1058157 36.4976321,64.7865963 34.9091182,66.2238342 L24.9620842,74.8470835 L24.9620842,38.8789018 Z M45.4991332,58.3191419 C50.3780737,58.3191419 54.387158,54.3100576 54.387158,49.3554404 C54.387158,44.5142996 50.3780737,40.429616 45.4991332,40.429616 C40.5823931,40.429616 36.5733088,44.5142996 36.5733088,49.3554404 C36.5733088,54.3100576 40.5823931,58.3191419 45.4991332,58.3191419 Z"/>
        </svg>
    ';
}
