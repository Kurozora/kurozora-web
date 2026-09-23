<?php

namespace App\Nova;

use App\Enums\MediaCollection;
use App\Scopes\PublicScope;
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Avatar;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Outl1ne\NovaColorField\Color;
use Ramsey\Uuid\Uuid;

class Provider extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Provider::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\Provider|null
     */
    public $resource;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'original_name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'slug', 'original_name'
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Streaming';

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

            Heading::make('Media'),

            Avatar::make('Logo')
                ->thumbnail(function () {
                    return $this->resource->getFirstMediaFullUrl(MediaCollection::Logo()) ?? asset('images/static/placeholders/platform_profile.webp');
                })->preview(function () {
                    return $this->resource->getFirstMediaFullUrl(MediaCollection::Logo()) ?? asset('images/static/placeholders/platform_profile.webp');
                })
                ->rounded()
                ->deletable(false)
                ->disableDownload()
                ->readonly()
                ->onlyOnPreview(),

            Images::make('Logo')
                ->showStatistics()
                ->setFileName(function ($originalFilename, $extension, $model) {
                    return Uuid::uuid4() . '.' . $extension;
                })
                ->setName(function ($originalFilename, $model) {
                    return $this->resource->original_name;
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

            Text::make('Slug')
                ->onlyOnForms()
                ->help('Used to identify the Provider. Leave empty to auto-generate from name.'),

            Text::make('Name', 'original_name')
                ->sortable()
                ->required(),

            Code::make('Alternative Names')
                ->json()
                ->sortable()
                ->rules(['json'])
                ->nullable()
                ->help('Other names or domains the provider is known by.'),

            Text::make('URL', 'url')
                ->nullable()
                ->hideFromIndex()
                ->help('The provider\'s homepage.'),

            Boolean::make('Public', 'is_public')
                ->default(true)
                ->sortable()
                ->help('Public providers are indexed, searchable, and shown to users. Turn off for piracy sites.'),
        ];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        $provider = $this->resource;
        $providerName = $provider->original_name;

        if (!is_string($providerName) || !strlen($providerName)) {
            $providerName = 'No Provider name';
        }

        return $providerName . ' (ID: ' . $provider->id . ')';
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
        return [];
    }

    /**
     * Build an "index" query for the given resource.
     *
     * @param NovaRequest $request
     * @param Builder     $query
     *
     * @return Builder
     */
    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        return parent::indexQuery($request, $query->withoutGlobalScope(new PublicScope));
    }

    /**
     * Build a "detail" query for the given resource.
     *
     * @param NovaRequest $request
     * @param Builder     $query
     *
     * @return Builder
     */
    public static function detailQuery(NovaRequest $request, $query): Builder
    {
        return parent::detailQuery($request, $query)->withoutGlobalScope(new PublicScope);
    }
}
