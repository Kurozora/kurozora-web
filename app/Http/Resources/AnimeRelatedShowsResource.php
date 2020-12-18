<?php

namespace App\Http\Resources;

use App\Models\AnimeRelations;
use App\Enums\AnimeRelationType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnimeRelatedShowsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var AnimeRelations $animeRelation */
        $animeRelation = $this->resource;

        return [
            'show'          => AnimeResourceBasic::make($animeRelation->related_anime),
            'attributes'    => [
                'type' => AnimeRelationType::getDescription($animeRelation->type)
            ]
        ];
    }
}
