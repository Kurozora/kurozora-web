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

        if ($includeInput = $request->input('include')) {
            // Include relation propagates to nested Resource objects.
            // To avoid loading unnecessary relations, we set it to
            // an empty value.
            $request->merge(['include' => '']);
            if (is_string($includeInput)) {
                $includeInput = explode(',', $includeInput);
            }
            $includes = array_unique($includeInput);

            $relationships = [];
            foreach ($includes as $include) {
                $relationships = match ($include) {
                    'show' => array_merge($relationships, $this->getShowRelationship()),
                    'season' => array_merge($relationships, $this->getSeasonRelationship()),
                    default => $relationships
                };
            }

            $resource = array_merge($resource, ['relationships' => $relationships]);
        }

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
