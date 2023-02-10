<?php

namespace App\Http\Resources;

use App\Models\Manga;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LiteratureResourceIdentity extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Manga|string $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id'    => $this->resource?->id ?? $this->resource,
            'uuid'  => $this->resource?->id ?? $this->resource,
            'type'  => 'literatures',
            'href'  => route('api.manga.view', $this->resource, false),
        ];
    }
}
