<?php

namespace App\Nova;

use App\Enums\AstrologicalSign;
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Avatar;
use Laravel\Nova\Fields\BelongsToMany;
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

class Person extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Person::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\Person|null
     */
    public $resource;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'full_name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'mal_id', 'first_name', 'last_name', 'slug'
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'People';

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
                ->help('The ID of the person as noted on MyAnimeList.'),

            Heading::make('Media'),

            Avatar::make('Profile')
                ->thumbnail(function () {
                    return $this->resource->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/person_poster.webp');
                })->preview(function () {
                    return $this->resource->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/person_poster.webp');
                })
                ->rounded()
                ->deletable(false)
                ->disableDownload()
                ->readonly()
                ->onlyOnPreview(),

            Images::make('Profile')
                ->showStatistics()
                ->setFileName(function ($originalFilename, $extension, $model) {
                    return Uuid::uuid4() . '.' . $extension;
                })
                ->setName(function ($originalFilename, $model) {
                    return $this->resource->full_name;
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

            Heading::make('Personal Information'),

            Text::make('Slug')
                ->onlyOnForms()
                ->help('Used to identify the Person in a URL: ' . config('app.url') . '/people/<strong>' . ($this->resource->slug ?? 'slug-identifier') . '</strong>. Leave empty to auto-generate from first and last name.'),

            Text::make('First name')
                ->help('The first name of the person as known in the industry. Usually in English.')
                ->rules(['required', 'max:255'])
                ->sortable(),

            Text::make('Last name')
                ->help('The last name of the person as known in the industry. Usually in English.')
                ->rules(['max:255'])
                ->nullable()
                ->sortable(),

            Text::make('Family name')
                ->help('The person’s official last name if the name they go by in the industry is different. Usually in Japanese.')
                ->rules(['max:255'])
                ->nullable()
                ->sortable(),

            Text::make('Given name')
                ->help('The person’s official first name if the name they go by in the industry is different. Usually in Japanese.')
                ->rules(['max:255'])
                ->nullable()
                ->sortable(),

            Code::make('Aliases', 'alternative_names')
                ->json()
                ->sortable()
                ->help('Other names the person is known by. For example ["Nakamura Hiroaki", "中村 博昭"]')
                ->rules(['json'])
                ->nullable(),

            Date::make('Birthdate')
                ->rules(['date'])
                ->nullable()
                ->sortable(),

            Date::make('Deceased date')
                ->rules(['date'])
                ->nullable()
                ->sortable(),

            Select::make('Astrological Sign')
                ->options(AstrologicalSign::asSelectArray())
                ->displayUsing(function (?AstrologicalSign $astrologicalSign) {
                    return $astrologicalSign?->key;
                })
                ->sortable(),

            Textarea::make('About')
                ->hideFromIndex()
                ->help('A long description of the person.')
                ->nullable(),

            Text::make('Short Description')
                ->hideFromIndex()
                ->help('A short description of the person.')
                ->nullable(),

            Code::make('Website URLs')
                ->json()
                ->hideFromIndex()
                ->help('The URLs to the official website of the person. Separated by ","')
                ->nullable(),

            HasMany::make('Anime Cast'),

            HasMany::make('Game Cast'),

            HasMany::make('Media Staff'),

            BelongsToMany::make('Characters')
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
        $person = $this->resource;

        return $person->full_name . ' (ID: ' . $person->id . ')';
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
}
