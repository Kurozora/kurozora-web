<?php

namespace App\Nova;

use App\Enums\AstrologicalSign;
use App\Enums\CharacterStatus;
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Illuminate\Http\Request;
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

    /**
     * The icon of the resource.
     *
     * @var string
     */
    public static string $icon = '
        <svg class="sidebar-icon" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <path fill="var(--sidebar-icon)" d="M5.55749119,50.4442449 C8.62518575,50.4442449 11.1112857,48.2636991 11.1112857,45.572739 L11.1112857,6 L0.168626492,44.3904526 C-0.708105683,47.4650916 1.94466019,50.4442449 5.55749119,50.4442449 Z M99.8313735,44.3904526 L88.8887143,6 L88.8887143,45.572739 C88.8887143,48.2636991 91.3748143,50.4442449 94.4425088,50.4442449 C98.0553398,50.4442449 100.708106,47.4650916 99.8313735,44.3904526 Z M83.3331837,42.6005301 C83.3331837,21.6856935 50,6 50,6 C50,6 16.6668163,21.6856935 16.6668163,42.6005301 C16.6668163,56.9685212 12.6980841,67.9302775 11.2623267,73.9736532 C10.6668433,76.4788503 11.8855878,79.0725886 14.0956473,80.0916813 L42.1406603,93.765231 C43.6575788,94.5045332 45.3229441,94.8884898 47.0104301,94.8884898 L52.9930421,94.8884898 C54.6805345,94.8884898 56.3454576,94.504811 57.8628119,93.765231 L85.9078249,80.0916813 C88.1178844,79.0725886 89.336629,76.4788503 88.7411455,73.9736532 C87.3019159,67.9302775 83.3331837,56.9685212 83.3331837,42.6005301 L83.3331837,42.6005301 Z M54.166648,86.2947784 L54.166648,55.9997755 L69.4443572,50.4442449 L69.4443572,44.8887143 L30.5556428,44.8887143 L30.5556428,50.4442449 L45.833352,55.9997755 L45.833352,86.2947784 L19.9445794,73.6750434 C20.0782593,73.1854623 20.2188837,72.6733118 20.3664525,72.1368559 C22.2119303,65.4372332 25.0001122,55.3140147 25.0001122,42.6005301 C25.0001122,31.5866907 40.9514295,20.4287547 50,15.3853745 C59.0485705,20.4287547 74.9998878,31.5866907 74.9998878,42.6005301 C74.9998878,55.3140147 77.7880697,65.4372332 79.6335475,72.1351198 C79.7811163,72.6698396 79.9217407,73.1837262 80.0554206,73.6715712 L54.166648,86.2947784 Z"/>
        </svg>
    ';
}
