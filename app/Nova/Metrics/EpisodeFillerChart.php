<?php

namespace App\Nova\Metrics;

use App\Enums\EpisodeFillerKind;
use App\Models\Episode;
use DateInterval;
use DateTimeInterface;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;

class EpisodeFillerChart extends Partition
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
        return $this->count($request, Episode::class, 'filler_kind')
            ->label(function ($value) {
                return EpisodeFillerKind::getDescription((int) $value);
            })
            ->colors([
                EpisodeFillerKind::AnimeCanon => '#0a84ff',
                EpisodeFillerKind::MangaCanon => '#32d74b',
                EpisodeFillerKind::MixedCanonFiller => '#ffd60a',
                EpisodeFillerKind::Filler => '#ff453a'
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
        return 'episode-filler-chart';
    }

    /**
     * Get the name of the metric.
     *
     * @return string
     */
    public function name(): string
    {
        return 'Episode Filler';
    }
}
