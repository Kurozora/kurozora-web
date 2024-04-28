<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Http\Requests\NovaRequest;
use Validator;

class MangaCast extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\MangaCast::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\MangaCast|null
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
        'id',
    ];

    /**
     * The relationships that should be eager loaded on index queries.
     *
     * @var array
     */
    public static $with = [
        'manga.translations',
        'character.translations'
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Manga';

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request): array
    {
        return [
            Heading::make('Identification')
                ->onlyOnDetail(),

            ID::make()->sortable(),

            Heading::make('Meta information'),

            BelongsTo::make('Manga')
                ->searchable()
                ->sortable()
                ->required(),

            BelongsTo::make('Character')
                ->searchable()
                ->sortable()
                ->required(),

            BelongsTo::make('Cast Role')
                ->sortable()
                ->help('If you’re not sure what role the character has, choose "Supporting Character".'),
        ];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        $mangaCast = $this->resource;

        $mangaTitle = $mangaCast->manga?->title ?? 'Unknown';
        $characterName = $mangaCast->character?->name ?? 'Unknown';
        $personName = $mangaCast->person?->full_name ?? 'Unknown';

        return $personName . ' as ' . $characterName . ' in ' . $mangaTitle . ' (ID: ' . $mangaCast->id . ')';
    }

    /**
     * Handle any post-validation processing.
     *
     * @param NovaRequest $request
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     * @throws ValidationException
     */
    protected static function afterValidation(NovaRequest $request, $validator): void
    {
        $resourceID = $request->resourceId;
        $manga = $request->post('manga');
        $character = $request->post('character');
        $castRole = $request->post('castRole');

        $unique = Rule::unique(\App\Models\MangaCast::TABLE_NAME, 'cast_role_id')->where(function ($query) use ($resourceID, $manga, $character, $castRole) {
            if ($resourceID) {
                $query->whereNotIn('id', [$resourceID]);
            }

            return $query->where([
                ['manga_id', $manga],
                ['character_id', $character],
                ['cast_role_id', $castRole],
            ]);
        });

        $uniqueValidator = Validator::make($request->only('castRole'), [
            'castRole'  => [$unique],
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
}
