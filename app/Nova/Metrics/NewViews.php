<?php

namespace App\Nova\Metrics;

use App\Models\View;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;

class NewViews extends Value
{
    /**
     * Calculate the value of the metric.
     *
     * @param NovaRequest $request
     * @return mixed
     */
    public function calculate(NovaRequest $request): mixed
    {
        return $this->count($request, View::class);
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges(): array
    {
        return [
            7 => '7 Days',
            30 => '30 Days',
            60 => '60 Days',
            365 => '365 Days',
            'MTD' => 'Month To Date',
            'QTD' => 'Quarter To Date',
            'YTD' => 'Year To Date',
        ];
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey(): string
    {
        return 'new-views-metric';
    }
}
