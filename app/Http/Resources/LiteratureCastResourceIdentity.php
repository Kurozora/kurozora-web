<?php

namespace App\Http\Resources;

use App\Models\MangaCast;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LiteratureCastResourceIdentity extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var MangaCast $resource
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
            'id' => (string) $this->resource->id,
            'type' => 'literature-cast',
            'href' => route('api.literature-cast.details', $this->resource, false),
        ];
    }
}
