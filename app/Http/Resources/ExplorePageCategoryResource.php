<?php

namespace App\Http\Resources;

use App\Anime;
use App\Enums\ExplorePageCategoryTypes;
use App\ExplorePageCategory;
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

        $baseResponse = [
            'title'     => $category->title,
            'position'  => $category->position,
            'type'      => $category->type,
            'size'      => $category->size
        ];

        // Add specific data per type
        return array_merge($baseResponse, $this->getTypeSpecificData($category));
    }

    /**
     * Returns specific data that should be added depending on the type of
     * category.
     *
     * @param ExplorePageCategory $category
     * @return array
     */
    private function getTypeSpecificData(ExplorePageCategory $category): array
    {
        // Genres category
        switch($category->type) {
            case ExplorePageCategoryTypes::Genres: {
                return [
                    'genres' => GenreResource::collection($category->genres)
                ];
            }

            case ExplorePageCategoryTypes::Shows: {
                return [
                    'shows' => AnimeResource::collection($category->animes)
                ];
            }

            case ExplorePageCategoryTypes::MostPopularShows: {
                return [
                    'shows' => AnimeResource::collection(Anime::mostPopular(10)->get())
                ];
            }
        }

        // Return nothing by default
        return [];
    }
}
