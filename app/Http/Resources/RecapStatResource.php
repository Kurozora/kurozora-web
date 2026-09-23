<?php

namespace App\Http\Resources;

use App\Models\Anime;
use App\Models\Provider;
use App\Models\RecapStat;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecapStatResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var RecapStat $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray(Request $request): array
    {
        $relationships = $this->getModelRelationship();

        return [
            'id' => (string) $this->resource->id,
            'type' => 'recap-stats',
            'attributes' => [
                'year' => $this->resource->year,
                'month' => $this->resource->month,
                'stat' => $this->resource->stat->value,
                'value' => $this->resource->value,
                'occurredAt' => $this->resource->occurred_at?->timestamp,
            ],
            'relationships' => $this->when(!empty($relationships), $relationships),
        ];
    }

    /**
     * Returns the relationship of the stat's model.
     *
     * @return array
     */
    protected function getModelRelationship(): array
    {
        $model = $this->resource->model;

        if ($model === null) {
            return [];
        }

        return match ($this->resource->model_type) {
            Anime::class => ['shows' => ['data' => [AnimeResourceIdentity::make($model)]]],
            Provider::class => ['providers' => ['data' => [ProviderResource::make($model)]]],
            default => [],
        };
    }
}
