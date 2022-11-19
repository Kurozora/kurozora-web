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
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class ScrapeAnimeSeason extends Action implements ShouldQueue
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
            Artisan::call('scrape:mal_anime_season', [
                'years' => $fields->get('years'),
                '--force' => $fields->get('force'),
            ]);
        } catch (Exception $e) {
            logger()->error($e->getMessage());
            return Action::danger(__('There was an error scraping this anime.'));
        }

        return Action::message('Scraped anime season!');
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
            Text::make('Years')
                ->default(now()->year)
                ->required()
                ->help('The year of the season to scrape. Scrape multiple years by separating with a comma. Examples:<br />2016<br />2022-2025<br />2016,2022-2025'),
            Boolean::make('Force')
                ->default(false)
                ->required()
                ->help('Scrape anime that’s already in the database.'),
        ];
    }
}
