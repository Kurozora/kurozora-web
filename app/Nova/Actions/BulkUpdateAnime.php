<?php

namespace App\Nova\Actions;

use App\Models\TvRating;
use App\Services\AnimeBulkUpdater;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;
use Throwable;

class BulkUpdateAnime extends Action
{
    use Queueable;

    /**
     * Perform the action on the given models.
     *
     * @param ActionFields $fields
     * @param Collection   $models
     *
     * @return ActionResponse
     * @throws Throwable
     */
    public function handle(ActionFields $fields, Collection $models): ActionResponse
    {
        AnimeBulkUpdater::handle(
            $models->pluck('id'),
            [
                'tv_rating_id' => $fields->tv_rating_id,
            ]
        );

        return Action::message('Anime updated successfully.');
    }

    /**
     * Get the fields available on the action.
     *
     * @param NovaRequest $request
     *
     * @return array
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Select::make('TV Rating', 'tv_rating_id')
                ->options(
                    TvRating::pluck('name', 'id')
                )
                ->rules('required')
        ];
    }
}
