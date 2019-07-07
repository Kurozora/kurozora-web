<?php

namespace App\Http\Resources;

use App\Anime;
use App\Enums\ExplorePageCategoryTypes;
use Illuminate\Http\Resources\Json\JsonResource;

class ExplorePageCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // Create the base response
        $baseResponse = [
            'id'    => $this->id,
            'title' => $this->title,
            'type'  => $this->type,
            'size'  => $this->size
        ];

        // Add specific data per type
        $endResponse = array_merge($baseResponse, $this->getTypeSpecificData());

        return $endResponse;
    }

    /**
     * Returns specific data that should be added depending on the type of
     * .. category.
     *
     * @return array
     */
    private function getTypeSpecificData() {
        // Genres category
        switch($this->type) {
            case ExplorePageCategoryTypes::Genres: {
                return [
                    'genres' => GenreResource::collection($this->genres)
                ];
            }

            case ExplorePageCategoryTypes::Shows: {
                return [
                    'shows' => AnimeResource::collection($this->animes)
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
