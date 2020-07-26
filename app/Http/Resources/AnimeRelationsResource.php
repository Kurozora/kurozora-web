<?php

namespace App\Http\Resources;

use App\AnimeRelations;
use App\Enums\AnimeRelationType;
use Illuminate\Http\Resources\Json\JsonResource;

class AnimeRelationsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
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
