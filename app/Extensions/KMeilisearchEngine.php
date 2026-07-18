<?php

namespace App\Extensions;

use Illuminate\Database\Eloquent\Collection;
use Laravel\Scout\Builder;
use Laravel\Scout\Engines\MeilisearchEngine;
use Meilisearch\Client as Meilisearch;
use Meilisearch\Exceptions\CommunicationException;
use Meilisearch\Exceptions\TimeOutException;

class KMeilisearchEngine extends MeilisearchEngine
{
    /**
     * Whether Meilisearch transport failures are logged and swallowed instead of thrown.
     *
     * @var bool
     */
    protected bool $toleratesOutage;

    /**
     * Create a new Meilisearch engine instance.
     *
     * @param Meilisearch $meilisearch
     * @param bool        $softDelete
     * @param bool        $toleratesOutage
     */
    public function __construct(Meilisearch $meilisearch, bool $softDelete = false, bool $toleratesOutage = false)
    {
        parent::__construct($meilisearch, $softDelete);

        $this->toleratesOutage = $toleratesOutage;
    }

    /**
     * Update the given model in the index.
     *
     * @param Collection $models
     *
     * @return void
     * @throws CommunicationException
     * @throws TimeOutException
     */
    public function update($models): void
    {
        $this->tolerateOutage(fn () => parent::update($models));
    }

    /**
     * Remove the given model from the index.
     *
     * @param Collection $models
     *
     * @return void
     * @throws CommunicationException
     * @throws TimeOutException
     */
    public function delete($models): void
    {
        $this->tolerateOutage(fn () => parent::delete($models));
    }

    /**
     * Run an index write, swallowing transport failures when tolerance is enabled.
     *
     * @param callable $operation
     *
     * @return void
     * @throws CommunicationException
     * @throws TimeOutException
     */
    private function tolerateOutage(callable $operation): void
    {
        try {
            $operation();
        } catch (CommunicationException | TimeOutException $exception) {
            if (!$this->toleratesOutage) {
                throw $exception;
            }

            logger()->channel('stderr')->warning('Meilisearch unreachable; skipped index write: ' . $exception->getMessage());
        }
    }

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
