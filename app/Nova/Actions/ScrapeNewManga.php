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

class ScrapeNewManga extends Action implements ShouldQueue
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
        try {
            Artisan::call('scrape:mal_manga', [
                'malID' => $fields->get('malID')
            ]);
        } catch (Exception $e) {
            logger()->error($e->getMessage());
            return Action::danger(__('There was an error scraping this manga.'));
        }

        return Action::message('Scraped the requested manga!');
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
            Text::make('MAL ID', 'malID')
                ->required()
                ->help('The id of the manga. Accepts an array of comma separated IDs.'),
        ];
    }
}
