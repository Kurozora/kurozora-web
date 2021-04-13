<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class FetchAnimeDetails extends Action
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Perform the action on the given models.
     *
     * @param ActionFields $fields
     * @param Collection $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $amountSuccess = 0;
        $amountFailed = 0;

        foreach($models as $model) {
            // Skip the Anime if dtails were already fetched
            if ($model->fetched_details) {
                $amountFailed++;
                continue;
            }

            Artisan::call('animes:fetch_details', ['id' => $model->id]);
            $amountSuccess++;
        }

        if (!$amountSuccess) {
            if ($amountFailed > 1) {
                return Action::danger('The details for these anime were already fetched.');
            }
            else {
                return Action::danger('The details for this anime were already fetched.');
            }
        }

        return Action::message('Details for ' . $amountSuccess .' anime were fetched.<br>Details for ' . $amountFailed . ' anime were already fetched.');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [];
    }
}
