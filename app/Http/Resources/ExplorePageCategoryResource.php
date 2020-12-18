<?php

namespace App\Http\Resources;

use App\Models\Anime;
use App\Enums\ExplorePageCategoryTypes;
use App\Models\ExplorePageCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExplorePageCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var ExplorePageCategory $category */
        $category = $this->resource;

        $resource = [
            'type'          => 'explore',
            'href'          => route('api.explore', [], false),
            'attributes'    => [
                'title'     => $category->title,
                'position'  => $category->position,
                'type'      => $category->type,
                'size'      => $category->size
            ]
        ];

        $relationships = [];
        // Add specific data per type
        $relationships = array_merge($relationships, $this->getTypeSpecificData($request, $category));

        // Merge relationships and return
        return array_merge($resource, ['relationships' => $relationships]);
    }

    /**
     * Returns specific data that should be added depending on the type of
     * category.
     *
     * @param Request $request
     * @param ExplorePageCategory $category
     * @return array
     */
    private function getTypeSpecificData(Request $request, ExplorePageCategory $category): array
    {
        // Genres category
        switch($category->type) {
            case ExplorePageCategoryTypes::Genres: {
                return [
                    'genres' => [
                        'data' => GenreResource::collection($category->genres)
                    ]
                ];
            }
            case ExplorePageCategoryTypes::Shows: {
                $request->merge(['include' => 'genres']);
                return [
                    'shows' => [
                        'data' => AnimeResourceBasic::collection($category->animes)
                    ]
                ];
            }
            case ExplorePageCategoryTypes::MostPopularShows: {
                $request->merge(['include' => 'genres']);
                return [
                    'shows' => [
                        'data' => AnimeResourceBasic::collection(Anime::mostPopular(10)->get())
                    ]
                ];
            }
        }

        // Return nothing by default
        return [];
    }
}
