<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Validator;

class MediaType extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\MediaType::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name',
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
            ID::make(__('ID'), 'id')->sortable(),

            Select::make('Type')
                ->options([
                    'anime' => 'anime',
                    'manga' => 'manga'
                ])
                ->sortable()
                ->required(),

            Text::make('Name')
                ->sortable()
                ->help('The name of the media type.')
                ->required(),

            Text::make('Description')
                ->sortable()
                ->help('An explanation of what the media type means.')
                ->required(),

            HasMany::make('Anime'),
        ];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        return $this->name . ' (ID: ' . $this->id . ')';
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
        $type = $request->post('type');
        $name = $request->post('name');

        $unique = Rule::unique(\App\Models\MediaType::TABLE_NAME)->where(function ($query) use($resourceID, $type, $name) {
            if ($resourceID) {
                $query->whereNotIn('id', [$resourceID]);
            }

            return $query->where('type', $type)->where('name', $name);
        });

        $uniqueValidator = Validator::make($request->only('name'), [
            'name' => [$unique],
        ], [
            'name' => __('validation.unique')
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
            <path fill="var(--sidebar-icon)" d="M9.04608393,71.4300696 L41.3234353,71.4300696 L41.3234353,65.7630319 L9.15169174,65.7630319 C6.72292836,65.7630319 5.66699442,64.7774551 5.66699442,62.2783346 L5.66699442,22.1165132 C5.66699442,19.6525712 6.72292836,18.6669944 9.15169174,18.6669944 L78.7753531,18.6669944 C81.1686495,18.6669944 82.2247277,19.6525712 82.2247277,22.1165132 L82.2247277,53.7602176 L87.8915058,53.7602176 L87.8915058,22.0460839 C87.8915058,15.8863011 84.8998852,13 78.8806005,13 L9.04608393,13 C3.0270875,13 0,15.8863011 0,22.0460839 L0,62.3839424 C0,68.5437613 3.0270875,71.4300696 9.04608393,71.4300696 Z M53.9598243,86.6007866 L92.5375286,86.6007866 C97.4654125,86.6007866 100,84.1016661 100,79.1034178 L100,66.4670287 C100,61.4336163 97.4654125,58.9344958 92.5375286,58.9344958 L53.9598243,58.9344958 C48.9264047,58.9344958 46.4976413,61.4336163 46.4976413,66.291071 L46.4976413,79.1034178 C46.4976413,84.1016661 48.9264047,86.6007866 53.9598243,86.6007866 Z M88.4191844,74.5979651 C86.1664581,74.5979651 84.3714857,72.7676196 84.3714857,70.5148932 C84.3714857,68.3325673 86.1664581,66.5022289 88.4191844,66.5022289 C90.6373089,66.5022289 92.4322812,68.3325673 92.4322812,70.5148932 C92.4322812,72.7676196 90.6373089,74.5979651 88.4191844,74.5979651 Z M26.2935335,83.3273049 L41.8865808,83.3273049 C41.4642217,81.7433319 41.3586138,80.5817686 41.3586138,77.625067 L26.2935335,77.625067 C24.7447391,77.625067 23.4424109,78.927424 23.4424109,80.5113682 C23.4424109,82.0601554 24.7447391,83.3273049 26.2935335,83.3273049 Z"/>
        </svg>
    ';
}
