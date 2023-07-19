<?php

namespace App\Http\Resources;

use App\Models\Anime;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnimeResourceIdentity extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Anime|int $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id'    => (int) ($this->resource?->id ?? $this->resource),
            'uuid'  => (string) ($this->resource?->id ?? $this->resource),
            'type'  => 'show',
            'href'  => route('api.anime.view', $this->resource, false),
        ];
    }
}
