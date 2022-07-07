<?php

namespace App\Extensions;

use Laravel\Scout\Builder;
use Laravel\Scout\Engines\MeiliSearchEngine;

class KMeiliSearchEngine extends MeiliSearchEngine {
    /**
     * Get the filter array for the query.
     *
     * @param Builder $builder
     * @return string
     */
    protected function filters(Builder $builder): string
    {
        $filters = collect($builder->wheres)->map(function ($operation, $key) {
            if (is_array($operation)) {
                [$operator,  $value] = $operation;
            } else {
                $operator = '=';
                $value = $operation;
            }

            if (is_bool($value)) {
                return sprintf('%s%s%s', $key, $operator, $value ? 'true' : 'false');
            }

            return is_numeric($value)
                ? sprintf('%s%s%s', $key, $operator, $value)
                : sprintf('%s%s"%s"', $key, $operator, $value);
        });

        foreach ($builder->whereIns as $key => $values) {
            $filters->push(sprintf('(%s)', collect($values)->map(function ($value) use ($key) {
                if (is_bool($value)) {
                    return sprintf('%s=%s', $key, $value ? 'true' : 'false');
                }

                return filter_var($value, FILTER_VALIDATE_INT) !== false
                    ? sprintf('%s=%s', $key, $value)
                    : sprintf('%s="%s"', $key, $value);
            })->values()->implode(' OR ')));
        }

        return $filters->values()->implode(' AND ');
    }
}
