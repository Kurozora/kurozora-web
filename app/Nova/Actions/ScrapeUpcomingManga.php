<?php

namespace App\Nova\Actions;

use Artisan;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Http\Requests\NovaRequest;

class ScrapeUpcomingManga extends Action implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    /**
     * Indicates if this action is only available on the resource index view.
     *
     * @var bool
     */
    public $onlyOnIndex = true;

    /**
     * Determine if the action is executable for the given request.
     *
     * @param Request $request
     * @param Model  $model
     * @return bool
     */
    public function authorizedToRun(Request $request, $model): bool
    {
        return $request->user()?->id == 2;
    }

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
            Artisan::call('scrape:mal_upcoming_manga', [
                'pages' => $fields->get('pages'),
                'skip' => $fields->get('skip'),
                '--force' => $fields->get('force'),
            ]);
        } catch (Exception $e) {
            logger()->error($e->getMessage());
            return Action::danger(__('There was an error scraping this manga.'));
        }

        return Action::message('Scraped upcoming manga!');
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
            Number::make('Pages')
                ->default(1)
                ->required()
                ->help('The number of pages to scrape.'),
            Number::make('Skip')
                ->default(0)
                ->required()
                ->help('The number of pages to skip.'),
            Boolean::make('Force')
                ->default(false)
                ->required()
                ->help('Scrape manga that’s already in the database.'),
        ];
    }
}
