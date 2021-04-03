<?php

namespace Laravel\Nova\Fields;

use Laravel\Nova\Http\Requests\NovaRequest;

class Select extends Field
{
    use Searchable;

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'select-field';

    /**
     * Set the options for the select menu.
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
            'options' => collect($options ?? [])->map(function ($label, $value) {
                if ($this->searchable && isset($label['group'])) {
                    return [
                        'label' => $label['group'].' - '.$label['label'],
                        'value' => $value,
                    ];
                }

                return is_array($label) ? $label + ['value' => $value] : ['label' => $label, 'value' => $value];
            })->values()->all(),
        ]);
    }

    /**
     * Display values using their corresponding specified labels.
     *
     * @return $this
     */
    public function displayUsingLabels()
    {
        return $this->displayUsing(function ($value) {
            return collect($this->meta['options'])
                    ->where('value', $value)
                    ->first()['label'] ?? $value;
        });
    }

    /**
     * Enable subtitles within the related search results.
     *
     * @return $this
     * @throws \Exception
     */
    public function withSubtitles()
    {
        throw new \Exception('The `withSubtitles` option is not available on Select fields.');
    }

    /**
     * Prepare the field for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return with(app(NovaRequest::class), function ($request) {
            return array_merge(parent::jsonSerialize(), [
                'searchable' => is_bool($this->searchable)
                    ? $this->searchable
                    : call_user_func($this->searchable, $request),
            ]);
        });
    }
}
