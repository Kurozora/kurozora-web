<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class GenerateColorsFromImage extends Action
{
    use InteractsWithQueue, Queueable;

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

        foreach($models as $model) {
            // Skip the image asset if colors were already fetched
            if ($model->background_color) {
                continue;
            }

            Artisan::call('anime_images:generate_colors', ['id' => $model->id]);
            $amountSuccess++;
        }

        if (!$amountSuccess) {
            return Action::danger('The colors for these image assets were already generated.');
        }

        return Action::message('Generated colors for ' . $amountSuccess .' image assets.');
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
