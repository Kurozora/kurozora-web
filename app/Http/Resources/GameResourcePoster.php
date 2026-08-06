<?php

namespace App\Http\Resources;

use App\Enums\MediaCollection;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResourcePoster extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Game $resource
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
        return array_merge(GameResourceIdentity::make($this->resource)->toArray($request), [
            'attributes' => [
                'title'  => $this->resource->title,
                'poster' => MediaResource::make($this->resource->media->firstWhere('collection_name', '=', MediaCollection::Poster)),
            ],
        ]);
    }
}
