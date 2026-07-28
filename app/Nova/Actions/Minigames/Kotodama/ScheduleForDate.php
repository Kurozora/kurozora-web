<?php

namespace App\Nova\Actions\Minigames\Kotodama;

use App\Models\Minigames\Kotodama\DailyPuzzle;
use App\Models\Minigames\Kotodama\Word;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Http\Requests\NovaRequest;

class ScheduleForDate extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * The display name of the action.
     *
     * @var string
     */
    public $name = 'Schedule for Date';

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
        $date = $fields->date;

        foreach ($models as $model) {
            /** @var Word $model */
            try {
                $existing = DailyPuzzle::where('category_id', $model->category_id)
                    ->where('puzzle_date', $date)
                    ->first();

                if ($existing) {
                    $existing->word_id = $model->id;
                    $existing->save();
                } else {
                    $nextNumber = (int) DailyPuzzle::where('category_id', $model->category_id)
                        ->max('puzzle_number') + 1;

                    DailyPuzzle::create([
                        'category_id' => $model->category_id,
                        'word_id' => $model->id,
                        'puzzle_date' => $date,
                        'puzzle_number' => $nextNumber,
                    ]);
                }

                $this->markAsFinished($model);
            } catch (Exception $e) {
                $this->markAsFailed($model, $e);
            }
        }

        return Action::message('Scheduled.');
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
        return [
            Date::make('Date')
                ->rules('required', 'date_format:Y-m-d'),
        ];
    }
}
