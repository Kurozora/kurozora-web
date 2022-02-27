<?php

namespace App\Http\Resources;

use App\Enums\ExploreCategoryTypes;
use App\Models\ExploreCategory;
use App\Models\Genre;
use App\Models\Theme;
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
            'href'          => route('api.explore', $this->resource, absolute: false),
            'attributes'    => [
                'title'         => $this->resource->title,
                'description'   => $this->resource->description,
                'slug'          => $this->resource->slug,
                'position'      => $this->resource->position,
                'type'          => $this->resource->type,
                'size'          => $this->resource->size
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
            case ExploreCategoryTypes::People:
                return [
                    'people' => [
                        'data' => PersonResource::collection($this->resource
                            ->peopleBornToday()
                            ->explore_category_items
                            ->pluck('model')
                        )
                    ]
                ];
            case ExploreCategoryTypes::Characters:
                return [
                    'characters' => [
                        'data' => CharacterResource::collection($this->resource
                            ->charactersBornToday()
                            ->explore_category_items
                            ->pluck('model')
                        )
                    ]
                ];
            case ExploreCategoryTypes::Genres:
                return [
                    'genres' => [
                        'data' => GenreResource::collection($this->resource
                            ->explore_category_items
                            ->pluck('model')
                        )
                    ]
                ];
            case ExploreCategoryTypes::Themes:
                return [
                    'themes' => [
                        'data' => ThemeResource::collection($this->resource
                            ->explore_category_items
                            ->pluck('model')
                        )
                    ]
                ];
            case ExploreCategoryTypes::Songs:
                $request->merge(['include' => 'shows']);

                $showSongs = [
                    'showSongs' => [
                        'data' => AnimeSongResource::collection($this->resource
                            ->explore_category_items
                            ->pluck('model')
                        )->toArray($request)
                    ]
                ];

                $request->offsetUnset('include');

                return $showSongs;
            case ExploreCategoryTypes::Shows:
                return [
                    'shows' => [
                        'data' => AnimeResource::collection($this->resource
                            ->explore_category_items
                            ->pluck('model')
                        )
                    ]
                ];
            case ExploreCategoryTypes::UpcomingShows:
                $model = null;

                if (!empty($request->input('genre_id'))) {
                    $model = Genre::find($request->input('genre_id'));
                } else if (!empty($request->input('theme_id'))) {
                    $model = Theme::find($request->input('theme_id'));
                }

                return [
                    'shows' => [
                        'data' => AnimeResource::collection($this->resource
                            ->upcoming_shows($model)
                            ->explore_category_items
                            ->pluck('model')
                        )
                    ]
                ];
            case ExploreCategoryTypes::MostPopularShows:
                $model = null;

                if (!empty($request->input('genre_id'))) {
                   $model = Genre::find($request->input('genre_id'));
                } else if (!empty($request->input('theme_id'))) {
                    $model = Theme::find($request->input('theme_id'));
                }

                return [
                    'shows' => [
                        'data' => AnimeResource::collection($this->resource
                            ->most_popular_shows($model)
                            ->explore_category_items
                            ->pluck('model')
                        )
                    ]
                ];
            default: // Return empty shows by default
                return [
                    'shows' => null
                ];
        }
    }
}
