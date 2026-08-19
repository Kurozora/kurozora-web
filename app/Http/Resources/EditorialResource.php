<?php

namespace App\Http\Resources;

use App\Models\Editorial;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EditorialResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Editorial $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->resource->id,
            'type' => 'editorials',
            'href' => '',
            'attributes' => [
                'body' => $this->resource->body,
                'byline' => $this->resource->byline,
                'publishedAt' => $this->resource->published_at?->timestamp,
            ],
        ];
    }
}
