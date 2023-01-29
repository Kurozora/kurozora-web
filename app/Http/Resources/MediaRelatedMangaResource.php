<?php

namespace App\Http\Resources;

use App\Models\MediaRelation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaRelatedMangaResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var MediaRelation
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
            'manga'         => LiteratureResourceBasic::make($this->resource->related),
            'attributes'    => [
                'relation'  => $this->resource->relation->only(['name', 'description']),
            ],
        ];
    }
}
