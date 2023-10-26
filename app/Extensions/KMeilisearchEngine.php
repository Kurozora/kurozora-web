<?php

namespace App\Extensions;

use Laravel\Scout\Builder;
use Laravel\Scout\Engines\MeilisearchEngine;

class KMeilisearchEngine extends MeilisearchEngine
{
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

        $whereInOperators = [
            'whereIns'    => 'IN',
            'whereNotIns' => 'NOT IN',
        ];

        foreach ($whereInOperators as $property => $operator) {
            if (property_exists($builder, $property)) {
                foreach ($builder->{$property} as $key => $values) {
                    $filters->push(sprintf('%s %s [%s]', $key, $operator, collect($values)->map(function ($value) {
                        if (is_bool($value)) {
                            return sprintf('%s', $value ? 'true' : 'false');
                        }

                        return filter_var($value, FILTER_VALIDATE_INT) !== false
                            ? sprintf('%s', $value)
                            : sprintf('"%s"', $value);
                    })->values()->implode(', ')));
                }
            }
        }

        return $filters->values()->implode(' AND ');
    }
}
