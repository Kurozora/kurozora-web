<?php

namespace App\Http\Resources;

use App\Models\Anime;
use App\Models\Studio;
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

        if ($request->input('include')) {
            $includes = array_unique(explode(',', $request->input('include')));

            $relationships = [];
            foreach ($includes as $include) {
                $relationships = match ($include) {
                    'shows' => array_merge($relationships, $this->getAnimeRelationship($request->input('anime'))),
                    default => $relationships,
                };
            }

            $resource = array_merge($resource, ['relationships' => $relationships]);
        }

        return $resource;
    }

    /**
     * Returns the anime relationship for the resource.
     *
     * @param ?Anime $excludingAnime
     * @return array
     */
    protected function getAnimeRelationship(?Anime $excludingAnime = null): array
    {
        /** @param Studio $studio */
        $studio = $this->resource;

        $whereRules = [];
        if ($excludingAnime)
            array_push($whereRules, ['animes.id', '!=', $excludingAnime->id]);

        return [
            'shows' => [
                'href' => route('api.studios.anime', $studio, false),
                'data' => AnimeResourceBasic::collection($studio->getAnime($whereRules))
            ]
        ];
    }
}
