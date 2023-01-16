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
            'id'            => $this->resource->id,
            'type'          => 'explore',
            'href'          => route('api.explore.details', $this->resource, absolute: false),
            'attributes'    => [
                'title'         => $this->getTypeSpecificTitle($request),
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
     * Returns specific title that should be added depending on the type of
     * category.
     *
     * @param Request $request
     * @return string
     */
    private function getTypeSpecificTitle(Request $request): string
    {
        return match ($this->resource->type) {
            ExploreCategoryTypes::AnimeSeason => season_of_year()->key . ' ' . now()->year,
            default => $this->resource->title,
        };
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
        switch ($this->resource->type) {
            case ExploreCategoryTypes::People:
                return [
                    'people' => [
                        'data' => PersonResourceIdentity::collection($this->resource
                            ->peopleBornToday()
                            ->explore_category_items
                            ->pluck('model_id')
                        )
                    ]
                ];
            case ExploreCategoryTypes::Characters:
                return [
                    'characters' => [
                        'data' => CharacterResourceIdentity::collection($this->resource
                            ->charactersBornToday()
                            ->explore_category_items
                            ->pluck('model_id')
                        )
                    ]
                ];
            case ExploreCategoryTypes::Genres:
                return [
                    'genres' => [
                        'data' => GenreResourceIdentity::collection($this->resource
                            ->explore_category_items
                            ->pluck('model_id')
                        )
                    ]
                ];
            case ExploreCategoryTypes::Themes:
                return [
                    'themes' => [
                        'data' => ThemeResourceIdentity::collection($this->resource
                            ->explore_category_items
                            ->pluck('model_id')
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
                        'data' => AnimeResourceIdentity::collection($this->resource
                            ->explore_category_items
                            ->pluck('model_id')
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
                        'data' => AnimeResourceIdentity::collection($this->resource
                            ->upcoming_shows($model)
                            ->explore_category_items
                            ->pluck('model_id')
                        )
                    ]
                ];
            case ExploreCategoryTypes::NewShows:
                $model = null;

                if (!empty($request->input('genre_id'))) {
                    $model = Genre::find($request->input('genre_id'));
                } else if (!empty($request->input('theme_id'))) {
                    $model = Theme::find($request->input('theme_id'));
                }

                return [
                    'shows' => [
                        'data' => AnimeResourceIdentity::collection($this->resource
                            ->newShows($model)
                            ->explore_category_items
                            ->pluck('model_id')
                        )
                    ]
                ];
            case ExploreCategoryTypes::RecentlyUpdateShows:
                $model = null;

                if (!empty($request->input('genre_id'))) {
                    $model = Genre::find($request->input('genre_id'));
                } else if (!empty($request->input('theme_id'))) {
                    $model = Theme::find($request->input('theme_id'));
                }

                return [
                    'shows' => [
                        'data' => AnimeResourceIdentity::collection($this->resource
                            ->recentlyUpdatedShows($model)
                            ->explore_category_items
                            ->pluck('model_id')
                        )
                    ]
                ];
            case ExploreCategoryTypes::AnimeContinuing:
                $model = null;

                if (!empty($request->input('genre_id'))) {
                    $model = Genre::find($request->input('genre_id'));
                } else if (!empty($request->input('theme_id'))) {
                    $model = Theme::find($request->input('theme_id'));
                }

                return [
                    'shows' => [
                        'data' => AnimeResourceIdentity::collection($this->resource
                            ->anime_continuing($model)
                            ->explore_category_items
                            ->pluck('model_id')
                        )
                    ]
                ];
            case ExploreCategoryTypes::AnimeSeason:
                $model = null;

                if (!empty($request->input('genre_id'))) {
                    $model = Genre::find($request->input('genre_id'));
                } else if (!empty($request->input('theme_id'))) {
                    $model = Theme::find($request->input('theme_id'));
                }

                return [
                    'shows' => [
                        'data' => AnimeResourceIdentity::collection($this->resource
                            ->anime_season($model)
                            ->explore_category_items
                            ->pluck('model_id')
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
                        'data' => AnimeResourceIdentity::collection($this->resource
                            ->most_popular_shows($model)
                            ->explore_category_items
                            ->pluck('model_id')
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
