<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Http\Requests\NovaRequest;
use Titasgailius\SearchRelations\SearchesRelations;
use Validator;

class MediaStaff extends Resource
{
    use SearchesRelations;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\MediaStaff::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\MediaStaff|null
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
     * The relationship columns that should be searched.
     *
     * @var array
     */
    public static array $searchRelations = [
        'person' => ['id', 'first_name', 'last_name', 'family_name', 'given_name'],
        'model' => ['id', 'original_title'],
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

            BelongsTo::make('Person')
                ->searchable()
                ->sortable()
                ->required(),

            BelongsTo::make('Staff Role', 'staff_role')
                ->searchable()
                ->sortable(),
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
        return $request->user()->can('viewMediaStaff');
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
        $modelType = $request->post('model_type');
        $modelID = $request->post('model_id');
        $person = $request->post('person');
        $staffRole = $request->post('staff_role');

        $unique = Rule::unique(\App\Models\MediaStaff::TABLE_NAME, 'staff_role_id')->where(function ($query) use($modelType, $resourceID, $modelID, $person, $staffRole) {
            if ($resourceID) {
                $query->whereNotIn('id', [$resourceID]);
            }

            return $query
                ->where([
                    ['model_type', $modelType],
                    ['model_id', $modelID],
                    ['person_id', $person],
                    ['staff_role_id', $staffRole]
                ]);
        });

        $uniqueValidator = Validator::make($request->only('staff_role'), [
            'staff_role' => [$unique],
        ], [
            'staff_role' => __('validation.unique')
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
