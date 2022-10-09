<?php

namespace App\Nova\Actions;

use App\Models\Anime;
use Artisan;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class ScrapeFiller extends Action
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
        $isOwner = auth()->user()->id == 2;
        $nextUpdateTime = $isOwner ? 0 : 15;

        if (!$isOwner && $models->count() != 1) {
            return Action::danger('You may only scrape 1 anime’s filler list at a time.');
        }

        $scrapeCount = 0;
        $nonScrapeCount = 0;

        /** @var Anime $model */
        foreach ($models as $model) {
            $elapsedUpdatedAt = $model->updated_at->diffInMinutes(now());

            if ($elapsedUpdatedAt >= $nextUpdateTime) {
                if (empty($model->filler_id)) {
                    $nonScrapeCount += 1;

                    if (!$isOwner) {
                        return Action::danger(__(':x has no Filler ID specified. Please add it, and try again.', ['x' => $model->original_title]));
                    }
                } else {
                    try {
                        Artisan::call('scrape:anime_filler', ['slug' => $model->filler_id]);

                        $this->markAsFinished($model);
                        $scrapeCount += 1;
                    } catch (Exception $e) {
                        $this->markAsFailed($model, $e);
                        $nonScrapeCount += 1;

                        if (!$isOwner) {
                            return Action::danger(__('There was an error scraping the anime filler list. Please check the error message in the "Actions" section at the bottom of the anime’s detail page.'));
                        }
                    }
                }
            } else {
                $this->markAsFailed($model, __('Please wait :x minutes before scraping again.', ['x' => $nextUpdateTime - $elapsedUpdatedAt]));
                $nonScrapeCount += 1;

                if (!$isOwner) {
                    return Action::danger(__('Please wait :x minutes before scraping again.', ['x' => $nextUpdateTime - $elapsedUpdatedAt]));
                }
            }
        }

        return Action::message('Scraped ' . $scrapeCount . '/' . $models->count() . ' anime fillers, and had ' . $nonScrapeCount . ' issues.');
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
