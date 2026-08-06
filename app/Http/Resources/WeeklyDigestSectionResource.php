<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WeeklyDigestSectionResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var array $resource
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
        $resource = [
            'id' => $this->resource['kind'],
            'type' => 'digestSection',
            'href' => '',
            'attributes' => [
                'kind' => $this->resource['kind'],
                'title' => $this->resource['title'] ?? null,
                'subtitle' => $this->resource['subtitle'] ?? null,
                'position' => $this->resource['position'],
                'momentum' => $this->resource['momentum'] ?? null,
            ]
        ];

        // Add additional data to the resource
        $relationships = $this->getKindSpecificData();
        if (!empty($relationships)) {
            $resource = array_merge($resource, ['relationships' => $relationships]);
        }

        return $resource;
    }

    /**
     * Returns specific relationship data that should be added depending on the kind of the section.
     *
     * @return array
     */
    private function getKindSpecificData(): array
    {
        if (isset($this->resource['shows'])) {
            return [
                'shows' => [
                    'data' => AnimeResourceIdentity::collection($this->resource['shows']),
                ]
            ];
        }

        if (isset($this->resource['games'])) {
            return [
                'games' => [
                    'data' => GameResourceIdentity::collection($this->resource['games']),
                ]
            ];
        }

        if (isset($this->resource['episodes'])) {
            return [
                'episodes' => [
                    'data' => EpisodeResourceIdentity::collection($this->resource['episodes']),
                ]
            ];
        }

        if (isset($this->resource['people'])) {
            return [
                'people' => [
                    'data' => PersonResourceIdentity::collection($this->resource['people']),
                ]
            ];
        }

        return [];
    }
}
