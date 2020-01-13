<?php

namespace Laravel\Nova\Fields;

use Laravel\Nova\Http\Requests\NovaRequest;

class BooleanGroup extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'boolean-group-field';

    /**
     * The text alignment for the field's text in tables.
     *
     * @var string
     */
    public $textAlign = 'center';

    /**
     * Set the options for the field.
     *
     * @param  array|\Closure|\Illuminate\Support\Collection
     * @return $this
     */
    public function options($options)
    {
        if (is_callable($options)) {
            $options = $options();
        }

        return $this->withMeta([
            'options' => with(collect($options), function ($options) {
                return $options->map(function ($label, $name) use ($options) {
                    return $options->isAssoc()
                        ? ['label' => $label, 'name' => $name]
                        : ['label' => $label, 'name' => $label];
                })->values()->all();
            }),
        ]);
    }

    /**
     * Hydrate the given attribute on the model based on the incoming request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $requestAttribute
     * @param  object  $model
     * @param  string  $attribute
     * @return void
     */
    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        if ($request->exists($requestAttribute)) {
            $model->{$attribute} = json_decode($request[$requestAttribute], true);
        }
    }
}
