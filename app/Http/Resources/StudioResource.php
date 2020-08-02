<?php

namespace App\Http\Resources;

use App\Studio;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudioResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Studio $studio */
        $studio = $this->resource;

        $resource = StudioResourceBasic::make($studio)->toArray($request);

        if($request->input('include')) {
            $includes = array_unique(explode(',', $request->input('include')));

            $relationships = [];
            foreach ($includes as $include) {
                switch ($include) {
                    case 'anime':
                        $relationships = array_merge($relationships, $this->getAnimeRelationship());
                        break;
                }
            }

            $resource = array_merge($resource, ['relationships' => $relationships]);
        }

        return $resource;
    }

    /**
     * Returns the anime relationship for the resource.
     *
     * @return array
     */
    protected function getAnimeRelationship(): array
    {
        /** @param Studio $studio */
        $studio = $this->resource;

        return [
            'anime' => [
                'data' => AnimeResourceBasic::collection($studio->getAnime()),
                'href' => route('api.studios.anime', $studio, false)
            ]
        ];
    }
}
