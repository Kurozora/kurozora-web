<?php

namespace Laravel\Nova\Tests\Fixtures;

use Laravel\Nova\Actions\Action;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueuedUpdateStatusAction extends Action implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return string|void
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $this->markAsFailed($models->where('id', 1)->first(), 'Test Message');
        $this->markAsFinished($models->where('id', 2)->first());
    }
}
