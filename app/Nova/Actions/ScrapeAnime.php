<?php

namespace App\Nova\Actions;

use App\Enums\MediaCollection;
use App\Models\Anime;
use Artisan;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class ScrapeAnime extends Action implements ShouldQueue
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

        /** @var Anime $model */
        foreach ($models as $model) {
            if (empty($model->mal_id)) {
                $this->markAsFailed($model, $model->original_title . ' has no MAL ID specified. Please add it, and try again.');
                $nonScrapeCount++;
            } else {
                try {
                    Artisan::call('scrape:mal_anime', ['malID' => $model->mal_id]);

                    if ($model->tvdb_id) {
                        if (empty($model->getFirstMedia(MediaCollection::Banner))) {
                            Artisan::call('scrape:tvdb_banner', ['tvdbID' => $model->tvdb_id]);
                        }
                        Artisan::call('scrape:tvdb_episode', ['tvdbID' => $model->tvdb_id]);
                    }

                    $this->markAsFinished($model);
                    $scrapeCount++;
                } catch (Exception $e) {
                    $this->markAsFailed($model, $e);
                    $nonScrapeCount++;
                }
            }
        }

        return Action::message('Scraped ' . $scrapeCount . '/' . $models->count() . ' anime, and had ' . $nonScrapeCount . ' issues.');
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
