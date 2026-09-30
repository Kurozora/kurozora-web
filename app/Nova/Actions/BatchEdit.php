<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\FieldCollection;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class BatchEdit extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public $name = 'Batch Edit';

    /**
     * Perform the action on the given models.
     *
     * @param ActionFields $fields
     * @param Collection   $models
     *
     * @return ActionResponse
     */
    public function handle(ActionFields $fields, Collection $models): ActionResponse
    {
        $mode = $fields->get('edit_mode');
        $field = $fields->get('field_name');
        $value = $fields->get('value');

        $models->each(function ($model, $index) use ($mode, $field, $value) {
            match ($mode) {
                'single' => $model->$field = $value,
                'increment' => $model->$field = is_numeric($value) ? (int)$value + $index : $value,
                'individual' => $model->$field = data_get(json_decode($value, true), (string)$model->getKey()),
            };

            $model->save();
        });

        return Action::message('Batch edit applied.');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<int, Field>
     */
    public function fields(NovaRequest $request): array
    {
        $resourceClass = $request->resource();
        $resource = new $resourceClass($request->newResource());

        $availableFields = FieldCollection::make($resource->fields($request))
            ->authorized($request)
            ->applyDependsOn($request)
            ->onlyUpdateFields($request, $resource)
            ->mapWithKeys(fn($field) => [$field->attribute => $field->name]);

        return [
            Select::make('Edit Mode')
                ->options([
                    'single' => 'Assign Single Value to All',
                    'increment' => 'Assign Incrementing Value',
                    'individual' => 'Assign Per Model',
                ])
                ->displayUsingLabels()
                ->rules('required'),

            Select::make('Field Name')
                ->options($availableFields)
                ->rules('required'),

            Textarea::make('Value')
                ->placeholder("Depends on mode:\n- Single: 5\n- Increment: 1 (start value)\n- Individual: JSON like {\"1\": 5, \"2\": 6}")
                ->rules('required'),
        ];
    }
}
