<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;

class Season extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = 'App\Models\AnimeSeason';

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
        'id', 'title'
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

            Text::make('Season Title', 'title')
                ->sortable(),

            Number::make('Season Number', 'number')
                ->rules('required', 'min:0')
                ->hideFromIndex()
                ->help('You should use season number <strong>0</strong> for "Specials".'),

            HasMany::make('Episodes'),
        ];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        $seasonName = $this->title;

        if (!is_string($seasonName) || !strlen($seasonName))
            $seasonName = 'No season title';

        return $seasonName . ' (ID: ' . $this->id . ')';
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
    public function lenses(Request $request)
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
            <path fill="var(--sidebar-icon)" d="M31.2484742,87.5006103 C27.7916117,87.5006103 24.9987794,90.2934427 24.9987794,93.7503052 C24.9987794,97.2071676 27.7916117,100 31.2484742,100 C34.7053367,100 37.498169,97.2071676 37.498169,93.7503052 C37.498169,90.2934427 34.7053367,87.5006103 31.2484742,87.5006103 Z M93.7454226,37.5030516 C97.202285,37.5030516 99.9951174,34.7102192 99.9951174,31.2533568 C99.9951174,27.7964943 97.202285,25.0036619 93.7454226,25.0036619 C90.2885601,25.0036619 87.4957277,27.7964943 87.4957277,31.2533568 C87.4957277,34.7102192 90.2885601,37.5030516 93.7454226,37.5030516 Z M74.9963381,56.2521361 C74.9963381,52.7952737 72.2035057,50.0024413 68.7466432,50.0024413 C65.2897808,50.0024413 62.4969484,52.7952737 62.4969484,56.2521361 C62.4969484,59.7089986 65.2897808,62.501831 68.7466432,62.501831 C72.2035057,62.501831 74.9963381,59.7089986 74.9963381,56.2521361 Z M46.8727113,68.7515258 C43.4158488,68.7515258 40.6230165,71.5443582 40.6230165,75.0012206 C40.6230165,78.4580831 43.4158488,81.2509155 46.8727113,81.2509155 C50.3295738,81.2509155 53.1224061,78.4580831 53.1224061,75.0012206 C53.1224061,71.5443582 50.3295738,68.7515258 46.8727113,68.7515258 Z M78.1211855,68.7515258 C74.664323,68.7515258 71.8714906,71.5443582 71.8714906,75.0012206 C71.8714906,78.4580831 74.664323,81.2509155 78.1211855,81.2509155 C81.5780479,81.2509155 84.3708803,78.4580831 84.3708803,75.0012206 C84.3708803,71.5443582 81.5780479,68.7515258 78.1211855,68.7515258 Z M93.7454226,87.5006103 C90.2885601,87.5006103 87.4957277,90.2934427 87.4957277,93.7503052 C87.4957277,97.2071676 90.2885601,100 93.7454226,100 C97.202285,100 99.9951174,97.2071676 99.9951174,93.7503052 C99.9951174,90.2934427 97.202285,87.5006103 93.7454226,87.5006103 Z M93.7454226,50.0024413 C90.2885601,50.0024413 87.4957277,52.7952737 87.4957277,56.2521361 C87.4957277,59.7089986 90.2885601,62.501831 93.7454226,62.501831 C97.202285,62.501831 99.9951174,59.7089986 99.9951174,56.2521361 C99.9951174,52.7952737 97.202285,50.0024413 93.7454226,50.0024413 Z M62.4969484,87.5006103 C59.0400859,87.5006103 56.2472536,90.2934427 56.2472536,93.7503052 C56.2472536,97.2071676 59.0400859,100 62.4969484,100 C65.9538108,100 68.7466432,97.2071676 68.7466432,93.7503052 C68.7466432,90.2934427 65.9538108,87.5006103 62.4969484,87.5006103 Z M49.9975587,39.0654753 C53.0052244,39.0654753 55.7394658,40.295884 57.7315561,42.2684439 L64.3523265,35.6476735 C60.6806308,31.9759777 55.6027538,29.6909331 49.9975587,29.6909331 C38.8066989,29.6909331 29.6860505,38.8115815 29.6860505,50.0024413 C29.6860505,55.6076363 31.9710952,60.6855134 35.6427909,64.3572091 L42.2635613,57.7364387 C40.2910014,55.7443484 39.0605927,53.0101069 39.0605927,50.0024413 C39.0605927,43.9675797 43.9626971,39.0654753 49.9975587,39.0654753 Z M78.2774279,12.289439 L64.4304477,14.9650896 L56.540208,3.26644207 C53.5911332,-1.08881402 46.4039842,-1.08881402 43.4549094,3.26644207 L35.5646697,14.9650896 L21.7372199,12.289439 C19.1396904,11.8011816 16.5031004,12.6019237 14.6477223,14.4768322 C12.7923441,16.3517406 11.9720717,18.9883306 12.4798594,21.5663298 L15.15551,35.4328402 L3.47639275,43.3426102 C1.28899956,44.8269127 0,47.2681998 0,49.9047898 C0,52.5413798 1.30852986,54.9826669 3.47639275,56.4474391 L15.15551,64.3572091 L12.4798594,78.2237195 C11.9720717,80.8017187 12.7923441,83.457839 14.6477223,85.3132171 L14.6672526,85.3327474 L22.1473561,77.8526439 L22.0887652,77.8721742 L25.5260974,60.0605439 L10.5268297,49.9047898 L25.5260974,39.7490357 L22.0887652,21.9374054 L39.8613349,25.3747376 L49.9975587,10.3559397 L60.1337825,25.3747376 L77.9063522,21.9374054 L77.8672916,22.1327084 L85.4450466,14.5549534 C85.405986,14.5158928 85.405986,14.4768322 85.3669254,14.4573019 C83.492017,12.6019237 80.8163664,11.8011816 78.2774279,12.289439 Z"/>
        </svg>
    ';
}
