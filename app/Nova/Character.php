<?php

namespace App\Nova;

use App\Enums\AstrologicalSign;
use App\Enums\CharacterStatus;
use App\Enums\MediaCollection;
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Illuminate\Http\Request;
use Kiritokatklian\NovaAstrotranslatable\HandlesTranslatable;
use Laravel\Nova\Fields\Avatar;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Outl1ne\NovaColorField\Color;
use Ramsey\Uuid\Uuid;

class Character extends Resource
{
    use HandlesTranslatable;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Character::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\Character|null
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
        'id', 'mal_id', 'slug'
    ];

    /**
     * The relationships that should be eager loaded on index queries.
     *
     * @var array
     */
    public static $with = [
        'translations',
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Character';

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
                ->help('The ID of the character as noted on MyAnimeList.'),

            Heading::make('Media'),

            Avatar::make('Profile')
                ->thumbnail(function () {
                    return $this->resource->getFirstMediaFullUrl(MediaCollection::Profile()) ?? asset('images/static/placeholders/person_poster.webp');
                })->preview(function () {
                    return $this->resource->getFirstMediaFullUrl(MediaCollection::Profile()) ?? asset('images/static/placeholders/person_poster.webp');
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

            Heading::make('Personal Information'),

            Text::make('Slug')
                ->onlyOnForms()
                ->help('Used to identify the Person in a URL: ' . config('app.url') . '/characters/<strong>' . ($this->resource->slug ?? 'slug-identifier') . '</strong>. Leave empty to auto-generate from name.'),

            Text::make('Name')
                ->rules('required')
                ->sortable()
                ->translatable(),

            Number::make('Nicknames', function () {
                return count($this->nicknames ?? []);
            })
                ->onlyOnIndex(),

            Code::make('Nicknames')
                ->json()
                ->sortable()
                ->help('Other names the character is known by. For example ["Pirate King", "Straw Hat"]')
                ->rules(['json'])
                ->nullable(),

            Textarea::make('About')
                ->translatable()
                ->help('A short description of the character.'),

            Text::make('Debut')
                ->rules('max:255')
                ->help('When did the character first appear? E.g. Episode 1')
                ->sortable(),

            Select::make('Status')
                ->options(CharacterStatus::asSelectArray())
                ->displayUsing(function (?CharacterStatus $characterStatus) {
                    return $characterStatus?->key;
                })
                ->required()
                ->sortable()
                ->help('The life status of the character. E.g. Alive, or Dead.'),

            Text::make('Blood Type')
                ->rules('nullable', 'max:3', 'string')
                ->help('The official blood type of the character. E.g. O, AB, or even fictional ones like F.')
                ->hideFromIndex(),

            Text::make('Favorite Food')
                ->rules('max:255')
                ->help('The official favorite food of the character. E.g. Almond Jelly.')
                ->hideFromIndex(),

            Heading::make('Body Information')
                ->onlyOnForms(),

            Number::make('Height')
                ->help('The current height of the character in cm. E.g. 10.25 or 277.')
                ->step(0.01)
                ->hideFromIndex(),

            Number::make('Weight')
                ->help('The current weight of the character in grams. E.g. 429 or 70000 (70kg).')
                ->step(0.01)
                ->hideFromIndex(),

            Number::make('Bust')
                ->help('The current bust size of the character if it applies. E.g. 50 or 60.4')
                ->step(0.01)
                ->hideFromIndex(),

            Number::make('Waist')
                ->help('The current waist size of the character if it applies. E.g. 50 or 60.4')
                ->step(0.01)
                ->hideFromIndex(),

            Number::make('Hip')
                ->help('The current hip size of the character if it applies. E.g. 50 or 60.4')
                ->step(0.01)
                ->hideFromIndex(),

            Heading::make('Birth Information')
                ->onlyOnForms(),

            Number::make('Age')
                ->help('The current age of the character in years. E.g. 17.5 or 25. If the age is something crazy like 3 and a half trillion years old, then write it out as a real number.')
                ->step(0.01)
                ->sortable(),

            Number::make('Birth Day')
                ->min(1)
                ->max(31)
                ->rules('nullable', 'min:1', 'max:31')
                ->hideFromIndex(),

            Number::make('Birth Month')
                ->min(1)
                ->max(12)
                ->rules('nullable', 'min:1', 'max:12')
                ->hideFromIndex(),

            Select::make('Astrological Sign')
                ->options(AstrologicalSign::asSelectArray())
                ->displayUsing(function (?AstrologicalSign $astrologicalSign) {
                    return $astrologicalSign?->key;
                })
                ->sortable(),

            HasMany::make('Translations', 'translations', CharacterTranslation::class),

            HasMany::make('Cast', 'cast', AnimeCast::class),

            BelongsToMany::make('Anime')
                ->searchable(),

            BelongsToMany::make('People')
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
        $character = $this->resource;
        $characterName = $character->name;

        if (!is_string($characterName) || !strlen($characterName)) {
            $characterName = 'No character title';
        }

        return $characterName . ' (ID: ' . $character->id . ')';
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
