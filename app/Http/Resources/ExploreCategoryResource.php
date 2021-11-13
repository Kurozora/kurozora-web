<?php

namespace App\Http\Resources;

use App\Enums\ExploreCategoryTypes;
use App\Models\Anime;
use App\Models\ExploreCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExploreCategoryResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var ExploreCategory
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
        $resource = [
            'type'          => 'explore',
            'href'          => route('api.explore', absolute: false),
            'attributes'    => [
                'title'     => $this->resource->title,
                'position'  => $this->resource->position,
                'type'      => $this->resource->type,
                'size'      => $this->resource->size
            ]
        ];

        $relationships = [];
        // Add specific data per type
        $relationships = array_merge($relationships, $this->getTypeSpecificData($request));

        // Merge relationships and return
        return array_merge($resource, ['relationships' => $relationships]);
    }

    /**
     * Returns specific data that should be added depending on the type of
     * category.
     *
     * @param Request $request
     * @return array
     */
    private function getTypeSpecificData(Request $request): array
    {
        // Genres category
        switch ($this->resource->type) {
            case ExplorePageCategoryTypes::Genres: {
                return [
                    'genres' => [
                        'data' => GenreResource::collection($this->resource->genres)
                    ]
                ];
            }
            case ExplorePageCategoryTypes::Shows: {
                return [
                    'shows' => [
                        'data' => AnimeResource::collection($this->resource->animes)
                    ]
                ];
            }
            case ExplorePageCategoryTypes::MostPopularShows: {
                return [
                    'shows' => [
                        'data' => AnimeResource::collection(Anime::mostPopular()->get())
                    ]
                ];
            }
            default: return []; // Return nothing by default
        }
    }
}
