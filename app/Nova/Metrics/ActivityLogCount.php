<?php

namespace App\Nova\Metrics;

use DateInterval;
use DateTimeInterface;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Nova;
use Spatie\Activitylog\Models\Activity;

class ActivityLogCount extends Value
{
    /**
     * Calculate the value of the metric.
     *
     * @param NovaRequest $request
     * @return mixed
     */
    public function calculate(NovaRequest $request): mixed
    {
        return $this->count($request, Activity::class);
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges(): array
    {
        return [
            7 => Nova::__('7 Days'),
            30 => Nova::__('30 Days'),
            60 => Nova::__('60 Days'),
            365 => Nova::__('365 Days'),
            'MTD' => Nova::__('Month To Date'),
            'QTD' => Nova::__('Quarter To Date'),
            'YTD' => Nova::__('Year To Date'),
            'ALL' => Nova::__('All Time'),
        ];
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return DateTimeInterface|DateInterval|float|int
     */
    public function cacheFor(): DateTimeInterface|DateInterval|float|int
    {
        return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey(): string
    {
        return 'activity-log-count';
    }

    /**
     * Get the name of the metric.
     *
     * @return string
     */
    public function name(): string
    {
        return 'Amount of activity logged';
    }
}
