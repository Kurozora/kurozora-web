<?php

namespace App\Nova;

use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Outl1ne\NovaColorField\Color;
use Ramsey\Uuid\Uuid;

class Theme extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Theme::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\Theme|null
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
        'id', 'name', 'slug'
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Theme';

    /**
     * Determine if this resource uses Laravel Scout.
     *
     * @return bool
     */
    public static function usesScout(): bool
    {
        return false;
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request): array
    {
        return [
            Heading::make('Identification'),

            ID::make()->sortable(),

            Number::make('MAL ID')
                ->hideFromIndex()
                ->help('Used to identify the Theme on <a target="_blank" href="https://myanimelist.net/anime/genre/' . ($this->resource->mal_id ?? 'slug-identifier') . '">MyAnimeList</a>'),

            Heading::make('Media'),

            Images::make('Symbol')
                ->showStatistics()
                ->setFileName(function($originalFilename, $extension, $model) {
                    return Uuid::uuid4() . '.' . $extension;
                })
                ->setName(function($originalFilename, $model) {
                    return $this->resource->name;
                }),
//                ->customPropertiesFields([
//                    Heading::make('Colors (automatically generated if empty)'),
//
//                    Color::make('Background Color')
//                        ->slider()
//                        ->help('The average background color of the image.'),
//
//                    Color::make('Text Color 1')
//                        ->slider()
//                        ->help('The primary text color that may be used if the background color is displayed.'),
//
//                    Color::make('Text Color 2')
//                        ->slider()
//                        ->help('The secondary text color that may be used if the background color is displayed.'),
//
//                    Color::make('Text Color 3')
//                        ->slider()
//                        ->help('The tertiary text color that may be used if the background color is displayed.'),
//
//                    Color::make('Text Color 4')
//                        ->slider()
//                        ->help('The final post-tertiary text color that may be used if the background color is displayed.'),
//
//                    Heading::make('Dimensions (automatically generated if empty)'),
//
//                    Number::make('Width')
//                        ->help('The maximum width available for the image.'),
//
//                    Number::make('Height')
//                        ->help('The maximum height available for the image.'),
//                ]),

            Heading::make('Meta information'),

            Text::make('Slug')
                ->onlyOnForms()
                ->help('Used to identify the theme in a URL: ' . config('app.url') . '/theme/<strong>' . ($this->resource->slug ?? 'slug-identifier') . '</strong>. Leave empty to auto-generate from name.'),

            Text::make('Name')
                ->rules('required')
                ->sortable(),

            Text::make('Description')
                ->hideFromIndex(),

            Color::make('Color')
                ->slider()
                ->rules('required'),

            Color::make('Background Color 1')
                ->slider()
                ->rules('required'),

            Color::make('Background Color 2')
                ->slider()
                ->rules('required'),

            Color::make('Text Color 1')
                ->slider()
                ->rules('required'),

            Color::make('Text Color 2')
                ->slider()
                ->rules('required'),

            Boolean::make('Is NSFW')
                ->rules('required')
                ->sortable(),

            Boolean::make('Is NSFW')
                ->rules('required')
                ->sortable(),

            BelongsTo::make('TV rating', 'tv_rating')
                ->sortable()
                ->help('The TV rating of the theme. For example NR, G, PG-12, etc.')
                ->required(),

            BelongsToMany::make('Animes')
                ->searchable(),
        ];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        $theme = $this->resource;
        $themeName = $theme->name;

        if (!is_string($themeName) || !strlen($themeName)) {
            $themeName = 'No theme title';
        }

        return $themeName . ' (ID: ' . $theme->id . ')';
    }

    /**
     * Determine if this resource is available for navigation.
     *
     * @param Request $request
     * @return bool
     */
    public static function availableForNavigation(Request $request): bool
    {
        return $request->user()->can('viewTheme');
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
            <path fill="var(--sidebar-icon)" d="M10.103186,48.2477404 C11.9487502,48.2477404 13.6340655,47.7893727 15.159132,46.8726373 C16.6841985,45.9559549 17.8997895,44.7377533 18.8059051,43.2180323 C19.7120739,41.6983114 20.1651583,40.0189563 20.1651583,38.1799672 C20.1651583,36.3464433 19.7120739,34.6684413 18.8059051,33.1459612 C17.8997895,31.623481 16.6841985,30.4038733 15.159132,29.4871378 C13.6340655,28.5704024 11.9487502,28.1120347 10.103186,28.1120347 C8.23014601,28.1120347 6.5311194,28.5635576 5.00610618,29.4666032 C3.48109295,30.3695958 2.26550193,31.5823322 1.3593331,33.1048123 C0.453111035,34.6272925 0,36.3190107 0,38.1799672 C0,40.0189563 0.459979984,41.6983114 1.37993995,43.2180323 C2.29989992,44.7377533 3.52241314,45.9559549 5.04747961,46.8726373 C6.57249284,47.7893727 8.2577283,48.2477404 10.103186,48.2477404 Z M10.103186,42.1618525 C9.02321678,42.1618525 8.08038694,41.7672907 7.27469647,40.9781671 C6.46895275,40.1889904 6.06608089,39.2562571 6.06608089,38.1799672 C6.06608089,37.0487591 6.46895275,36.102283 7.27469647,35.3405388 C8.08038694,34.5788477 9.02321678,34.1980022 10.103186,34.1980022 C11.1833149,34.1980022 12.1193557,34.5925905 12.9113083,35.3817672 C13.7032609,36.1709439 14.0992371,37.1036772 14.0992371,38.1799672 C14.0992371,39.2562571 13.7101564,40.1889904 12.931995,40.9781671 C12.1538336,41.7672907 11.2108972,42.1618525 10.103186,42.1618525 Z M50.0041933,32.0943977 C51.8497574,32.0943977 53.5350195,31.6429014 55.0599795,30.7399088 C56.5849927,29.8368631 57.8005838,28.6186614 58.7067526,27.0853038 C59.6129746,25.5518931 60.0660857,23.8656666 60.0660857,22.0266244 C60.0660857,20.1931005 59.6129746,18.5150985 58.7067526,16.9926184 C57.8005838,15.4701383 56.5849927,14.2574284 55.0599795,13.3544889 C53.5350195,12.4514963 51.8497574,12 50.0041933,12 C48.1642733,12 46.4735001,12.4514963 44.9318736,13.3544889 C43.3902471,14.2574284 42.1663494,15.4701383 41.2601806,16.9926184 C40.354065,18.5150985 39.9010073,20.1931005 39.9010073,22.0266244 C39.9010073,23.8656666 40.3609606,25.5518931 41.2808673,27.0853038 C42.2008273,28.6186614 43.424725,29.8368631 44.9525603,30.7399088 C46.4803957,31.6429014 48.1642733,32.0943977 50.0041933,32.0943977 Z M50.0041933,26.0085098 C48.9240643,26.0085098 47.9880502,25.6139215 47.1961508,24.8247448 C46.4042515,24.0355681 46.0083018,23.1028613 46.0083018,22.0266244 C46.0083018,20.8954163 46.4042515,19.9489667 47.1961508,19.1872757 C47.9880502,18.4255315 48.9240643,18.0446594 50.0041933,18.0446594 C51.084269,18.0446594 52.0202831,18.4392478 52.8122357,19.2284245 C53.6041882,20.0176012 54.0001645,20.9503345 54.0001645,22.0266244 C54.0001645,23.1028613 53.6179528,24.0355681 52.8535292,24.8247448 C52.089159,25.6139215 51.1393803,26.0085098 50.0041933,26.0085098 Z M89.8962549,48.2477404 C91.7364943,48.2477404 93.427374,47.7893727 94.968894,46.8726373 C96.510414,45.9559549 97.7343117,44.7377533 98.640587,43.2180323 C99.5468623,41.6983114 100,40.0189563 100,38.1799672 C100,36.3190107 99.5455311,34.6272925 98.6365934,33.1048123 C97.7276557,31.5823322 96.5106803,30.3695958 94.9856671,29.4666032 C93.4606538,28.5635576 91.7641831,28.1120347 89.8962549,28.1120347 C88.0506907,28.1120347 86.365402,28.5704024 84.8403888,29.4871378 C83.3153756,30.4038733 82.0999975,31.623481 81.1942547,33.1459612 C80.2879794,34.6684413 79.8348417,36.3464433 79.8348417,38.1799672 C79.8348417,40.0189563 80.2879794,41.6983114 81.1942547,43.2180323 C82.0999975,44.7377533 83.3153756,45.9559549 84.8403888,46.8726373 C86.365402,47.7893727 88.0506907,48.2477404 89.8962549,48.2477404 Z M89.8962549,42.1618525 C88.7887034,42.1618525 87.8528756,41.7672907 87.0887716,40.9781671 C86.3241351,40.1889904 85.9418168,39.2562571 85.9418168,38.1799672 C85.9418168,37.1036772 86.3310573,36.1709439 87.1095382,35.3817672 C87.8874866,34.5925905 88.8163922,34.1980022 89.8962549,34.1980022 C91.0043389,34.1980022 91.9542773,34.5788477 92.7460701,35.3405388 C93.537863,36.102283 93.9337594,37.0487591 93.9337594,38.1799672 C93.9337594,39.2562571 93.537863,40.1889904 92.7460701,40.9781671 C91.9542773,41.7672907 91.0043389,42.1618525 89.8962549,42.1618525 Z M14.7426021,45.6899865 L27.108388,53.2603363 C28.3533185,54.0212315 29.8708504,54.3271548 31.6609838,54.1781064 C33.4511704,54.029058 34.9954859,53.0930083 36.2939303,51.3699575 L51.0375708,31.6825114 L45.4844247,29.3321731 L31.9976422,47.3138928 C31.6648443,47.8108624 31.2849488,48.1006552 30.8579557,48.1832713 C30.4309094,48.2659403 29.8991782,48.1114797 29.2627621,47.7198893 L17.8460093,40.7253048 L14.7426021,45.6899865 Z M85.257318,45.6478826 L82.1535114,40.6749233 L70.8628758,47.5863347 C70.1537766,48.0278024 69.5744952,48.194759 69.1250316,48.0872043 C68.6755681,47.9795965 68.2622064,47.6742303 67.8849467,47.1711056 L54.5736419,29.2489999 L49.0204958,31.6405667 L63.8063084,51.4118227 C65.043518,53.090143 66.5417476,54.0080193 68.3009974,54.1654513 C70.0600341,54.3229365 71.5034453,54.0629109 72.6312309,53.3853746 L85.257318,45.6478826 Z M19.6731498,74.1297168 L80.3851564,74.1297168 L80.3851564,68.2909607 L19.6731498,68.2909607 L19.6731498,74.1297168 Z M9.88457769,46.5221957 L17.830674,78.8391326 C18.6044691,81.8851169 19.8561887,84.1734243 21.5858327,85.7040546 C23.31553,87.2346849 25.7939693,88 29.0211508,88 L71.0369957,88 C74.2366481,88 76.7081386,87.2346849 78.4514673,85.7040546 C80.1947959,84.1734243 81.4397264,81.8851169 82.1862587,78.8391326 L90.1318758,46.5221957 L84.6614766,44.0811231 L76.3180994,78.0900886 C75.6647505,80.7766747 73.9208894,82.1199677 71.086516,82.1199677 L28.9713908,82.1199677 C26.1037909,82.1199677 24.346112,80.7766747 23.6983541,78.0900886 L15.354897,44.0811231 L9.88457769,46.5221957 Z" />
        </svg>
    ';
}
