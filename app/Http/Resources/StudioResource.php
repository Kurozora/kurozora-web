<?php

namespace App\Http\Resources;

use App\Models\Studio;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudioResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Studio $resource
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
        $resource = StudioResourceBasic::make($this->resource)->toArray($request);
        $include = $request->input('include');

        if ($include) {
            if (is_string($include)) {
                $includes = array_unique(explode(',', $include));
            } else if (is_array($include)) {
                $includes = $include;
            } else {
                $includes = [];
            }

            $relationships = [];
            foreach ($includes as $include) {
                $relationships = match ($include) {
                    'predecessors' => array_merge($relationships, $this->getPredecessorsRelationship()),
                    'successors' => array_merge($relationships, $this->getSuccessorRelationship()),
                    'shows' => array_merge($relationships, $this->getAnimeRelationship()),
                    'games' => array_merge($relationships, $this->getGamesRelationship()),
                    'literatures' => array_merge($relationships, $this->getMangaRelationship()),
                    default => $relationships,
                };
            }

            if (count($relationships)) {
                $resource = array_merge($resource, ['relationships' => $relationships]);
            }
        }

        return $resource;
    }

    /**
     * Returns the predecessors relationship for the resource.
     *
     * @return array
     */
    protected function getPredecessorsRelationship(): array
    {
        return [
            'studios' => [
                'href' => route('api.studios.predecessors', $this->resource, false),
                'data' => StudioResourceIdentity::collection($this->resource->predecessors)
            ]
        ];
    }

    /**
     * Returns the successor relationship for the resource.
     *
     * @return array
     */
    protected function getSuccessorRelationship(): array
    {
        return [
            'studios' => [
                'href' => route('api.studios.successors', $this->resource, false),
                'data' => StudioResourceIdentity::collection($this->resource->successor)
            ]
        ];
    }

    /**
     * Returns the anime relationship for the resource.
     *
     * @return array
     */
    protected function getAnimeRelationship(): array
    {
        return [
            'shows' => [
                'href' => route('api.studios.anime', $this->resource, false),
                'data' => AnimeResourceIdentity::collection($this->resource->anime)
            ]
        ];
    }

    /**
     * Returns the manga relationship for the resource.
     *
     * @return array
     */
    protected function getMangaRelationship(): array
    {
        return [
            'literatures' => [
                'href' => route('api.studios.literatures', $this->resource, false),
                'data' => LiteratureResourceIdentity::collection($this->resource->manga)
            ]
        ];
    }

    /**
     * Returns the games relationship for the resource.
     *
     * @return array
     */
    protected function getGamesRelationship(): array
    {
        return [
            'games' => [
                'href' => route('api.studios.games', $this->resource, false),
                'data' => GameResourceIdentity::collection($this->resource->games)
            ]
        ];
    }
}
