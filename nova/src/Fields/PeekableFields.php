<?php

namespace Laravel\Nova\Fields;

use Laravel\Nova\Http\Requests\NovaRequest;

trait PeekableFields
{
    /**
     * Indicates whether to show the field in the modal preview.
     *
     * @var (callable(\Laravel\Nova\Http\Requests\NovaRequest):bool)|bool
     */
    public $showWhenPeeking = false;

    /**
     * Show the field in the modal preview.
     *
     * @param  (callable(\Laravel\Nova\Http\Requests\NovaRequest):bool)|bool  $callback
     * @return $this
     */
    public function showWhenPeeking($callback = true)
    {
        $this->showWhenPeeking = $callback;

        return $this;
    }

    /**
     * Determine if the field is to be shown in the preview modal.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return bool
     */
    public function isShownWhenPeeking(NovaRequest $request): bool
    {
        if (is_callable($this->showWhenPeeking)) {
            $this->showWhenPeeking = call_user_func($this->showWhenPeeking, $request);
        }

        return $this->showWhenPeeking;
    }
}
