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
     * @param Request $request
     *
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) ($this->resource?->id ?? $this->resource),
            'type' => 'literatures',
            'href' => route('api.manga.view', $this->resource, false),
        ];
    }
}
