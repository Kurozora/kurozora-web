<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\MorphToActionTarget;
use Laravel\Nova\Fields\Status;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;

class ActionResource extends \Laravel\Nova\Actions\ActionResource
{
    /**
     * Indicates whether the resource should automatically poll for new resources.
     *
     * @var bool
     */
    public static $polling = false;

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request): array
    {
        return [
            ID::make('ID', 'id'),
            Text::make(__('Action Name'), 'name', function ($value) {
                return __($value);
            }),

            BelongsTo::make(__('Action Initiated By'), 'user', User::class),

            MorphToActionTarget::make(__('Action Target'), 'target'),

            Status::make(__('Action Status'), 'status', function ($value) {
                return __(ucfirst($value));
            })->loadingWhen([__('Waiting'), __('Running')])->failedWhen([__('Failed')]),

            $this->when(isset($this->original), function () {
                return KeyValue::make(__('Original'), 'original');
            }),

            $this->when(isset($this->changes), function () {
                return KeyValue::make(__('Changes'), 'changes');
            }),

            Textarea::make(__('Exception'), 'exception'),

            DateTime::make(__('Action Happened At'), 'created_at')->exceptOnForms(),
        ];
    }
}
