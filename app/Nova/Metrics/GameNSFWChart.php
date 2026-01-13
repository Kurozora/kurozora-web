<?php

namespace App\Nova\Metrics;

use App\Models\Game;
use DateInterval;
use DateTimeInterface;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;

class GameNSFWChart extends Partition
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
        return $this->count($request, Game::class, 'is_nsfw')
            ->label(function ($value) {
                return match ((bool) $value) {
                    false => 'Safe',
                    default => 'Not Safe',
                };
            })
            ->colors([
                false => '#3dd45e',
                true => '#d25561'
            ]);
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return  DateTimeInterface|DateInterval|float|int
     */
    public function cacheFor(): DateInterval|DateTimeInterface|float|int
    {
        return now()->addMinutes(20);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey(): string
    {
        return 'game-nsfw-chart';
    }

    /**
     * Get the name of the metric.
     *
     * @return string
     */
    public function name(): string
    {
        return 'Game';
    }
}
