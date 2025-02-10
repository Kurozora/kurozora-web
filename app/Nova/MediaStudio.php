<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Http\Requests\NovaRequest;
use Validator;

class MediaStudio extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\MediaStudio::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\MediaStudio|null
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
        'id', 'studio.id', 'studio.name', 'model.id', 'model.original_title'
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Media';

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

            MorphTo::make('Model')
                ->types([
                    Anime::class,
                    Game::class,
                    Manga::class,
                ])
                ->searchable()
                ->required(),

            BelongsTo::make('Studio')
                ->sortable()
                ->searchable()
                ->sortable(),

            Boolean::make('Is Licensor')
                ->required()
                ->sortable()
                ->help('The studio is responsible for licensing the media.'),

            Boolean::make('Is Producer')
                ->sortable()
                ->required()
                ->help('The studio is responsible for producing the media. Usually sponsors.'),

            Boolean::make('Is Studio')
                ->sortable()
                ->required()
                ->help('The studio responsible for creating (drawing) the media.'),

            Boolean::make('Is Publisher')
                ->sortable()
                ->required()
                ->help('The studio responsible for publishing (serializing) the media.'),

            Boolean::make('Is Developer')
                ->sortable()
                ->required()
                ->help('The studio responsible for developing the media.'),
        ];
    }

    /**
     * Determine if this resource is available for navigation.
     *
     * @param Request $request
     * @return bool
     */
    public static function availableForNavigation(Request $request): bool
    {
        return $request->user()->can('viewMediaStudio');
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
        $modelID = $request->post('model_id');
        $modelType = $request->post('model_type');
        $studio = $request->post('studio');

        $unique = Rule::unique(\App\Models\MediaStudio::TABLE_NAME, 'studio_id')->where(function ($query) use($resourceID, $modelID, $modelType, $studio) {
            if ($resourceID) {
                $query->whereNotIn('id', [$resourceID]);
            }

            return $query->where([
                ['model_id', $modelID],
                ['model_type', $modelType],
                ['studio_id', $studio]
            ]);
        });

        $uniqueValidator = Validator::make($request->only('studio'), [
            'studio' => [$unique],
        ], [
            'studio' => __('validation.unique')
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
