<?php

namespace App\Nova\Actions;

use Artisan;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Http\Requests\NovaRequest;

class FixAnimeAiringSeason extends Action implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    /**
     * Perform the action on the given models.
     *
     * @param ActionFields $fields
     * @param Collection $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models): mixed
    {
        try {
            Artisan::call('fix:anime_airing_season', [
                'year' => $fields->get('year')
            ]);
        } catch (Exception $e) {
            logger()->error($e->getMessage());
            return Action::danger(__('There was an error fixing anime airing season.'));
        }
        return Action::message('Fixed anime airing seasons!');
    }

    /**
     * Get the fields available on the action.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Number::make('Year')
                ->default(now()->year)
                ->required()
                ->help('The year whose anime should have its airing season fixed.'),
        ];
    }
}
