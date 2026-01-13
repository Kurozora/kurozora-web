<?php

namespace App\Nova\Metrics;

use App\Models\User;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;

class SubscribedUsers extends Partition
{
    /**
     * Calculate the value of the metric.
     *
     * @param NovaRequest $request
     *
     * @return mixed
     */
    public function calculate(NovaRequest $request): mixed
    {
        return $this->count($request, User::class, 'is_subscribed')
            ->label(function ($value) {
                return match ((bool) $value) {
                    false => 'Not Subscribed',
                    default => 'Is Subscribed',
                };
            });
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey(): string
    {
        return 'subscribed-users';
    }
}
