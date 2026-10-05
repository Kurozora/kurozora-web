<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use Validator;

class MediaFranchise extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\MediaFranchise::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\MediaFranchise|null
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
        'id', 'model.original_title'
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Franchise';

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
                ->searchable(),

            Text::make('Model Type')
                ->onlyOnIndex()
                ->onlyOnDetail()
                ->sortable(),

            BelongsTo::make('Franchise')
                ->sortable()
                ->required(),
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
        return $request->user()->can('viewMediaFranchise');
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
        $modelType = Nova::modelInstanceForKey($request->post('model_type'))->getMorphClass();
        $model = $request->post('model');
        $franchise = $request->post('franchise');

        $unique = Rule::unique(\App\Models\MediaFranchise::TABLE_NAME, 'franchise_id')->where(function ($query) use($request, $resourceID, $modelType, $model, $franchise) {
            if ($resourceID) {
                $query->whereNotIn('id', [$resourceID]);
            }

            $query->where([
                ['model_type', '=', $modelType],
                ['model_id', '=', $model],
                ['franchise_id', '=', $franchise]
            ]);
        });

        $uniqueValidator = Validator::make($request->only('franchise'), [
            'franchise' => [$unique],
        ], [
            'franchise' => __('validation.unique')
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
