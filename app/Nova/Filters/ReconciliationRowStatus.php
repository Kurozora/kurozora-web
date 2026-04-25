<?php

namespace App\Nova\Filters;

use App\Models\ReconciliationRow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\BooleanFilter;

class ReconciliationRowStatus extends BooleanFilter
{
    /**
     * Apply the filter to the given query.
     */
    public function apply(Request $request, $query, $value): Builder
    {
        $enabled = array_keys(array_filter($value, fn ($v) => $v));

        if (empty($enabled)) {
            return $query;
        }

        return $query->whereIn('status', $enabled);
    }

    /**
     * Get the filter's available options.
     */
    public function options(Request $request): array
    {
        return [
            'Present' => ReconciliationRow::STATUS_PRESENT,
            'Missing' => ReconciliationRow::STATUS_MISSING,
            'Orphan' => ReconciliationRow::STATUS_ORPHAN,
        ];
    }
}
