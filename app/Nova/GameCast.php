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

class GameCast extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\GameCast::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\GameCast|null
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
        'game.translations',
        'character.translations',
        'person',
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Game';

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

            BelongsTo::make('Game')
                ->searchable()
                ->sortable()
                ->required(),

            BelongsTo::make('Character')
                ->searchable()
                ->sortable()
                ->required(),

            BelongsTo::make('Person')
                ->searchable()
                ->sortable()
                ->nullable()
                ->help('Sometimes unknown. Leave empty in that case.'),

            BelongsTo::make('Cast Role')
                ->sortable()
                ->help('If you’re not sure what role the character has, choose "Supporting Character".'),

            BelongsTo::make('Language')
                ->sortable()
                ->nullable()
                ->help('Usually Japanese or English. Leave empty if unknown.'),
        ];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        $gameCast = $this->resource;

        $gameTitle = $gameCast->game?->title ?? 'Unknown';
        $characterName = $gameCast->character?->name ?? 'Unknown';
        $personName = $gameCast->person?->full_name ?? 'Unknown';

        return $personName . ' as ' . $characterName . ' in ' . $gameTitle . ' (ID: ' . $gameCast->id . ')';
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
        $game = $request->post('game');
        $character = $request->post('character');
        $person = $request->post('person');
        $castRole = $request->post('castRole');
        $language = $request->post('language');

        $unique = Rule::unique(\App\Models\GameCast::TABLE_NAME, 'language_id')->where(function ($query) use ($resourceID, $game, $character, $person, $castRole, $language) {
            if ($resourceID) {
                $query->whereNotIn('id', [$resourceID]);
            }

            return $query->where([
                ['game_id', $game],
                ['character_id', $character],
                ['person_id', $person],
                ['cast_role_id', $castRole],
                ['language_id', $language],
            ]);
        });

        $uniqueValidator = Validator::make($request->only('language'), [
            'language'  => [$unique],
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
