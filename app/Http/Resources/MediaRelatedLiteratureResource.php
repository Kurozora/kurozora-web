<?php

namespace App\Http\Resources;

use App\Models\MediaRelation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaRelatedLiteratureResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var MediaRelation $resource
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
            'literature'         => LiteratureResourceBasic::make($this->resource->related),
            'attributes'    => [
                'relation'  => $this->resource->relation->only(['name', 'description']),
            ],
        ];
    }
}
