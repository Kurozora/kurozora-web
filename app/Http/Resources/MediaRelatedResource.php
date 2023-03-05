<?php

namespace App\Http\Resources;

use App\Models\Game;
use App\Models\Manga;
use App\Models\MediaRelation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaRelatedResource extends JsonResource
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
        return match ($this->resource->related->getMorphClass()) {
            Manga::class => [
                'literature'    => LiteratureResourceBasic::make($this->resource->related),
                'attributes'    => [
                    'relation'  => $this->resource->relation->only(['name', 'description'])
                ]
            ],
            Game::class => [
                'game'          => GameResourceBasic::make($this->resource->related),
                'attributes'    => [
                    'relation'  => $this->resource->relation->only(['name', 'description'])
                ]
            ],
            default => [
                'show'          => AnimeResourceBasic::make($this->resource->related),
                'attributes'    => [
                    'relation'  => $this->resource->relation->only(['name', 'description']),
                ],
            ],
        };
    }
}
