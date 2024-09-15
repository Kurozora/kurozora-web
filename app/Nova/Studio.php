<?php

namespace App\Nova;

use App\Enums\MediaCollection;
use App\Enums\StudioType;
use App\Nova\Actions\FixStudioBanner;
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Avatar;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Outl1ne\NovaColorField\Color;
use Ramsey\Uuid\Uuid;

class Studio extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Studio::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\Studio|null
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
        'id', 'name', 'slug'
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Studios';

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
            Heading::make('Identification'),

            ID::make()->sortable(),

            Number::make('MAL ID')
                ->hideFromIndex()
                ->help('The id of the studio as noted on MyAnimeList.'),

            Heading::make('Media'),

            Avatar::make('Profile')
                ->thumbnail(function () {
                    return $this->resource->getFirstMediaFullUrl(MediaCollection::Profile()) ?? asset('images/static/placeholders/studio_profile.webp');
                })->preview(function () {
                    return $this->resource->getFirstMediaFullUrl(MediaCollection::Profile()) ?? asset('images/static/placeholders/studio_profile.webp');
                })
                ->rounded()
                ->deletable(false)
                ->disableDownload()
                ->readonly()
                ->onlyOnPreview(),

            Images::make('Banner')
                ->showStatistics()
                ->setFileName(function ($originalFilename, $extension, $model) {
                    return Uuid::uuid4() . '.' . $extension;
                })
                ->setName(function ($originalFilename, $model) {
                    return $this->resource->name;
                })
                ->customPropertiesFields([
                    Heading::make('Colors (automatically generated if empty)'),

                    Color::make('Background Color')
                        ->slider()
                        ->help('The average background color of the image.'),

                    Color::make('Text Color 1')
                        ->slider()
                        ->help('The primary text color that may be used if the background color is displayed.'),

                    Color::make('Text Color 2')
                        ->slider()
                        ->help('The secondary text color that may be used if the background color is displayed.'),

                    Color::make('Text Color 3')
                        ->slider()
                        ->help('The tertiary text color that may be used if the background color is displayed.'),

                    Color::make('Text Color 4')
                        ->slider()
                        ->help('The final post-tertiary text color that may be used if the background color is displayed.'),

                    Heading::make('Dimensions (automatically generated if empty)'),

                    Number::make('Width')
                        ->help('The maximum width available for the image.'),

                    Number::make('Height')
                        ->help('The maximum height available for the image.'),
                ]),

            Images::make('Profile')
                ->showStatistics()
                ->setFileName(function ($originalFilename, $extension, $model) {
                    return Uuid::uuid4() . '.' . $extension;
                })
                ->setName(function ($originalFilename, $model) {
                    return $this->resource->name;
                })
                ->customPropertiesFields([
                    Heading::make('Colors (automatically generated if empty)'),

                    Color::make('Background Color')
                        ->slider()
                        ->help('The average background color of the image.'),

                    Color::make('Text Color 1')
                        ->slider()
                        ->help('The primary text color that may be used if the background color is displayed.'),

                    Color::make('Text Color 2')
                        ->slider()
                        ->help('The secondary text color that may be used if the background color is displayed.'),

                    Color::make('Text Color 3')
                        ->slider()
                        ->help('The tertiary text color that may be used if the background color is displayed.'),

                    Color::make('Text Color 4')
                        ->slider()
                        ->help('The final post-tertiary text color that may be used if the background color is displayed.'),

                    Heading::make('Dimensions (automatically generated if empty)'),

                    Number::make('Width')
                        ->help('The maximum width available for the image.'),

                    Number::make('Height')
                        ->help('The maximum height available for the image.'),
                ]),

            Images::make('Logo')
                ->showStatistics()
                ->setFileName(function ($originalFilename, $extension, $model) {
                    return Uuid::uuid4() . '.' . $extension;
                })
                ->setName(function ($originalFilename, $model) {
                    return $this->resource->name;
                })
                ->customPropertiesFields([
                    Heading::make('Colors (automatically generated if empty)'),

                    Color::make('Background Color')
                        ->slider()
                        ->help('The average background color of the image.'),

                    Color::make('Text Color 1')
                        ->slider()
                        ->help('The primary text color that may be used if the background color is displayed.'),

                    Color::make('Text Color 2')
                        ->slider()
                        ->help('The secondary text color that may be used if the background color is displayed.'),

                    Color::make('Text Color 3')
                        ->slider()
                        ->help('The tertiary text color that may be used if the background color is displayed.'),

                    Color::make('Text Color 4')
                        ->slider()
                        ->help('The final post-tertiary text color that may be used if the background color is displayed.'),

                    Heading::make('Dimensions (automatically generated if empty)'),

                    Number::make('Width')
                        ->help('The maximum width available for the image.'),

                    Number::make('Height')
                        ->help('The maximum height available for the image.'),
                ]),

            Heading::make('Meta'),

            BelongsTo::make('TV rating', 'tv_rating')
                ->sortable()
                ->help('The TV rating of the anime. For example NR, G, PG-12, etc.')
                ->required(),

            Text::make('Slug')
                ->onlyOnForms()
                ->help('Used to identify the Studio in a URL: ' . config('app.url') . '/studios/<strong>' . ($this->resource->slug ?? 'slug-identifier') . '</strong>. Leave empty to auto-generate from name.'),

            Select::make('Type')
                ->options(StudioType::asSelectArray())
                ->displayUsing(function (StudioType $studioType) {
                    return $studioType->key;
                })
                ->rules('required')
                ->sortable(),

            Text::make('Name')
                ->rules('required')
                ->sortable(),

            Text::make('Japanese Name')
                ->hideFromIndex(),

            Code::make('Alternative Names')
                ->json()
                ->hideFromIndex()
                ->nullable(),

            Textarea::make('About')
                ->help('A description of the studio.'),

            Text::make('Address')
                ->help('The address of the studio.'),

            Date::make('Founded At')
                ->displayUsing(function ($founded) {
                    return $founded?->format('Y-m-d');
                })
                ->help('The date on which the studio was founded. For example: 2015-12-03'),

            Date::make('Defunct At')
                ->displayUsing(function ($founded) {
                    return $founded?->format('Y-m-d');
                })
                ->help('The date on which the studio went bankrupt. For example: 2015-12-03'),

            BelongsTo::make('TV rating', 'tv_rating')
                ->sortable()
                ->help('The TV rating of the studio. For example NR, G, PG-12, etc.')
                ->required(),

            Boolean::make('Is NSFW')
                ->sortable()
                ->help('NSFW: Not Safe For Work (not suitable for watchers under the age of 18).'),

            Code::make('Social URLs')
                ->json()
                ->hideFromIndex()
                ->help('The URLs to the official social media of the studio. Separated by ","')
                ->nullable(),

            Code::make('Website URLs')
                ->json()
                ->hideFromIndex()
                ->help('The URLs to profile websites of the studio, for example Wikipedia. Separated by ","')
                ->nullable(),

            HasMany::make('Media Studios'),

            BelongsToMany::make('Anime')
                ->searchable(),

            BelongsToMany::make('Manga')
                ->searchable(),

            BelongsToMany::make('Games')
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
        $studio = $this->resource;
        $studioName = $studio->name;

        if (!is_string($studioName) || !strlen($studioName)) {
            $studioName = 'No Studio name';
        }

        return $studioName . ' (ID: ' . $studio->id . ')';
    }

    /**
     * Get the cards available for the request.
     *
     * @param Request $request
     *
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
     *
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
     *
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
     *
     * @return array
     */
    public function actions(Request $request): array
    {
        return [
            FixStudioBanner::make()
                ->canSee(function ($request) {
                    return $request->user()->can('updateStudio');
                })->showInline(),
        ];
    }
}
