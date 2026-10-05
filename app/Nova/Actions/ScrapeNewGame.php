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
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class ScrapeNewGame extends Action implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    /**
     * Indicates if this action is only available on the resource index view.
     *
     * @var bool
     */
    public $onlyOnIndex = true;

    /**
     * Perform the action on the given models.
     *
     * @param ActionFields $fields
     * @param Collection $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models): mixed
    {
        $slugs = collect(explode(',', $fields->get('igdbSlug')))
            ->map(fn (string $slug) => trim($slug))
            ->filter()
            ->values()
            ->all();

        if (empty($slugs)) {
            return Action::danger(__('Please provide at least one IGDB slug.'));
        }

        try {
            Artisan::call('scrape:igdb_games', ['slugs' => $slugs]);
        } catch (Exception $e) {
            logger()->error($e->getMessage());
            return Action::danger(__('There was an error scraping this game.'));
        }

        return Action::message('Scraped the requested game!');
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
            Text::make('IGDB Slug', 'igdbSlug')
                ->required()
                ->help('The slug of the game. Accepts an array of comma separated slugs.'),
        ];
    }
}
