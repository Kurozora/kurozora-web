<?php

namespace App\Http\Resources;

use App\Enums\ExploreCategoryTypes;
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
        return match ($this->resource->type) {
            ExploreCategoryTypes::People => [
                'people' => [
                    'data' => PersonResource::collection($this->resource
                        ->peopleBornToday()
                        ->explore_category_items
                        ->pluck('model')
                    )
                ]
            ],
            ExploreCategoryTypes::Characters => [
                'characters' => [
                    'data' => CharacterResource::collection($this->resource
                        ->charactersBornToday()
                        ->explore_category_items
                        ->pluck('model')
                    )
                ]
            ],
            ExploreCategoryTypes::Genres => [
                'genres' => [
                    'data' => GenreResource::collection($this->resource
                        ->explore_category_items
                        ->pluck('model')
                    )
                ]
            ],
            ExploreCategoryTypes::Shows => [
                'shows' => [
                    'data' => AnimeResource::collection($this->resource
                        ->explore_category_items
                        ->pluck('model')
                    )
                ]
            ],
            ExploreCategoryTypes::UpcomingShows => [
                'shows' => [
                    'data' => AnimeResource::collection($this->resource
                        ->upcoming_shows()
                        ->explore_category_items
                        ->pluck('model')
                    )
                ]
            ],
            ExploreCategoryTypes::MostPopularShows => [
                'shows' => [
                    'data' => AnimeResource::collection($this->resource
                        ->most_popular_shows()
                        ->explore_category_items
                        ->pluck('model')
                    )
                ]
            ],
            default => [
                'shows' => null
            ], // Return empty shows by default
        };
    }
}
