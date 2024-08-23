<?php

namespace App\Http\Resources;

use App\Models\Episode;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EpisodeResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Episode $resource
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
        $resource = EpisodeResourceBasic::make($this->resource)->toArray($request);

        // Include relation propagates to nested Resource objects.
        // To avoid loading unnecessary relations, we set it to
        // an empty value.
        $request->merge(['include' => '']);

        $relationships = [];
        $relationships = array_merge($relationships, $this->getShowRelationship());
        $relationships = array_merge($relationships, $this->getSeasonRelationship());

        $resource = array_merge($resource, ['relationships' => $relationships]);

        return $resource;
    }

    /**
     * Returns the show relationship for the resource.
     *
     * @return array
     */
    protected function getShowRelationship(): array
    {
        return [
            'shows' => [
                'data' => AnimeResourceIdentity::collection([$this->resource->anime])
            ]
        ];
    }

    /**
     * Returns the season relationship for the resource.
     *
     * @return array
     */
    protected function getSeasonRelationship(): array
    {
        return [
            'seasons' => [
                'data' => SeasonResourceIdentity::collection([$this->resource->season])
            ]
        ];
    }
}
