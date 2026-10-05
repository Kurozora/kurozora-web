<?php

namespace App\Nova\Actions;

use App\Models\Game;
use Artisan;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class ScrapeGame extends Action implements ShouldQueue
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
        $scrapeCount = 0;
        $nonScrapeCount = 0;

        /** @var Game $model */
        foreach ($models as $model) {
            if (empty($model->igdb_slug)) {
                $this->markAsFailed($model, $model->original_title . ' has no IGDB slug specified. Please add it, and try again.');
                $nonScrapeCount++;
            } else {
                try {
                    Artisan::call('scrape:igdb_games', ['slugs' => [$model->igdb_slug]]);

                    $this->markAsFinished($model);
                    $scrapeCount++;
                } catch (Exception $e) {
                    $this->markAsFailed($model, $e);
                    $nonScrapeCount++;
                }
            }
        }

        return Action::message('Scraped ' . $scrapeCount . '/' . $models->count() . ' games, and had ' . $nonScrapeCount . ' issues.');
    }

    /**
     * Get the fields available on the action.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request): array
    {
        return [];
    }
}
