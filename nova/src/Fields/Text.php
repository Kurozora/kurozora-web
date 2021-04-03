<?php

namespace Laravel\Nova\Fields;

use Laravel\Nova\Http\Requests\NovaRequest;

class Text extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'text-field';

    /**
     * The field's suggestions callback.
     *
     * @var array|callable
     */
    public $suggestions;

    /**
     * Set the callback or array to be used to determine the field's suggestions list.
     *
     * @param  array|callable  $suggestions
     * @return $this
     */
    public function suggestions($suggestions)
    {
        $this->suggestions = $suggestions;

        return $this;
    }

    /**
     * Resolve the display suggestions for the field.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function resolveSuggestions(NovaRequest $request)
    {
        if (is_callable($this->suggestions)) {
            return call_user_func($this->suggestions, $request) ?? null;
        }

        return $this->suggestions;
    }

    /**
     * Display the field as raw HTML using Vue.
     *
     * @return $this
     */
    public function asHtml()
    {
        return $this->withMeta(['asHtml' => true]);
    }

    /**
     * Prepare the element for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $request = app(NovaRequest::class);

        if ($request->isCreateOrAttachRequest()
            || $request->isUpdateOrUpdateAttachedRequest()
            || $request->isActionRequest() === true
        ) {
            return array_merge(parent::jsonSerialize(), [
                'suggestions' => $this->resolveSuggestions($request),
            ]);
        }

        return parent::jsonSerialize();
    }
}
