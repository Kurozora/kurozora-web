<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Validator;

class Song extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Song::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\Song|null
     */
    public $resource;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'title', 'artist'
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Music';

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request): array
    {
        return [
            ID::make(__('ID'), 'id')->sortable(),

            Number::make('MAL ID')
                ->hideFromIndex()
                ->help('The ID of the Song as noted on MyAnimeList.'),

            Text::make('Title')
                ->sortable()
                ->required(),

            Text::make('Artist')
                ->sortable(),

            HasMany::make('Anime', 'anime_songs', AnimeSong::class),
        ];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        $song = $this->resource;

        return $song->title . ' by ' . $song->artist ?? 'Unknown' . ' (ID: ' . $song->id . ')';
    }

    /**
     * Handle any post-validation processing.
     *
     * @param NovaRequest $request
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     * @throws ValidationException
     */
    protected static function afterValidation(NovaRequest $request, $validator)
    {
        $resourceID = $request->resourceId;
        $title = $request->post('title');
        $artist = $request->post('artist');

        $unique = Rule::unique(\App\Models\Song::TABLE_NAME)->where(function ($query) use($resourceID, $title, $artist) {
            if ($resourceID) {
                $query->whereNotIn('id', [$resourceID]);
            }

            return $query->where('title', $title)->where('artist', $artist);
        });

        $uniqueValidator = Validator::make($request->only('title'), [
            'title' => [$unique],
        ], [
            'title' => __('validation.unique')
        ]);

        $uniqueValidator->validate();
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
            <path fill="var(--sidebar-icon)" d="M80.7345065,22.6381679 L80.7345065,3.91996275 C80.7345065,1.28148637 78.5938732,-0.411057638 76.0549042,0.0867854062 L50.4668117,5.66236242 C47.2807385,6.3593223 45.5383388,8.10172198 45.5383388,10.8895615 L45.6379482,66.2475058 C45.8868188,68.6868654 44.7418715,70.2799019 42.5513825,70.7279913 L34.6359534,72.3707815 C24.6795003,74.4616612 20,79.5394461 20,87.0565804 C20,94.6732935 25.8743032,100 34.1382123,100 C41.456189,100 52.4083282,94.6235092 52.4083282,80.1368272 L52.4083282,34.5858912 C52.4083282,31.9475168 52.9061712,31.3998181 55.2459214,30.902077 L77.9964207,25.9238505 C79.6890667,25.5753705 80.7345065,24.2809582 80.7345065,22.6381679 Z"/>
        </svg>
    ';
}
