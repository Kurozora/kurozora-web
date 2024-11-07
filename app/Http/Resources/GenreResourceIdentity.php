<?php

namespace App\Http\Resources;

use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GenreResourceIdentity extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Genre|int $resource
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
        return [
            'id' => (string) ($this->resource?->id ?? $this->resource),
            'type' => 'genres',
            'href' => route('api.genres.details', $this->resource, false),
        ];
    }
}
