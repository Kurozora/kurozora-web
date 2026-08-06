<?php

namespace App\Nova\Actions\Minigames\Kotodama;

use App\Models\Minigames\Kotodama\DailyPuzzle;
use App\Services\Minigames\Kotodama\PuzzleResolver;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class RegeneratePuzzle extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * The display name of the action.
     *
     * @var string
     */
    public $name = 'Regenerate Puzzle';

    /**
     * Perform the action on the given models.
     *
     * @param ActionFields $fields
     * @param Collection   $models
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models): mixed
    {
        foreach ($models as $model) {
            /** @var DailyPuzzle $model */
            try {
                $newWord = PuzzleResolver::pickNextWord($model->category);
                $model->word_id = $newWord->id;
                $model->save();

                $this->markAsFinished($model);
            } catch (Exception $e) {
                $this->markAsFailed($model, $e);
            }
        }

        return Action::message('Regenerated.');
    }

    /**
     * Get the fields available on the action.
     *
     * @param NovaRequest $request
     *
     * @return array
     */
    public function fields(NovaRequest $request): array
    {
        return [];
    }
}
