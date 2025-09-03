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
     * @var ExploreCategory $resource
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
            'id' => (string) $this->resource->id,
            'type' => 'explore',
            'href' => route('api.explore.details', $this->resource, absolute: false),
            'attributes' => [
                'title' => $this->getTypeSpecificTitle($request),
                'description' => $this->resource->description,
                'slug' => $this->resource->slug,
                'secondarySlug' => $this->resource->secondary_slug,
                'type' => $this->resource->type,
                'size' => $this->resource->size,
                'position' => $this->resource->position,
            ]
        ];

        $relationships = [];
        // Add specific data per type
        $relationships = array_merge($relationships, $this->getTypeSpecificData($request));

        // Merge relationships and return
        return array_merge($resource, ['relationships' => $relationships]);
    }

    /**
     * Returns specific title that should be added
     * depending on the type of the category.
     *
     * @param Request $request
     *
     * @return string
     */
    private function getTypeSpecificTitle(Request $request): string
    {
        return match ($this->resource->type) {
            ExploreCategoryTypes::ShowsSeason => season_of_year(today()->addDays(3))->key . ' ' . today()->addDays(3)->year,
            default => $this->resource->title,
        };
    }

    /**
     * Returns specific data that should be added
     * depending on the type of the category.
     *
     * @param Request $request
     *
     * @return array
     */
    private function getTypeSpecificData(Request $request): array
    {
        $nextPageURL = str_replace($request->root(), '', $this->resource->next_page_url ?? '');

        switch ($this->resource->type) {
            case ExploreCategoryTypes::UpNextEpisodes:
                return [
                    'episodes' => [
                        'data' => EpisodeResourceIdentity::collection($this->resource
                            ->exploreCategoryItems
                            ->pluck('model')
                        ),
                        'next' => empty($nextPageURL) ? null : $nextPageURL,
                    ]
                ];
            case ExploreCategoryTypes::People:
                return [
                    'people' => [
                        'data' => PersonResourceIdentity::collection($this->resource
                            ->exploreCategoryItems
                            ->pluck('model')
                        ),
                        'next' => empty($nextPageURL) ? null : $nextPageURL,
                    ]
                ];
            case ExploreCategoryTypes::Characters:
                return [
                    'characters' => [
                        'data' => CharacterResourceIdentity::collection($this->resource
                            ->exploreCategoryItems
                            ->pluck('model')
                        ),
                        'next' => empty($nextPageURL) ? null : $nextPageURL,
                    ]
                ];
            case ExploreCategoryTypes::Genres:
                return [
                    'genres' => [
                        'data' => GenreResourceIdentity::collection($this->resource
                            ->exploreCategoryItems
                            ->pluck('model')
                        ),
                        'next' => empty($nextPageURL) ? null : $nextPageURL,
                    ]
                ];
            case ExploreCategoryTypes::Themes:
                return [
                    'themes' => [
                        'data' => ThemeResourceIdentity::collection($this->resource
                            ->exploreCategoryItems
                            ->pluck('model')
                        ),
                        'next' => empty($nextPageURL) ? null : $nextPageURL,
                    ]
                ];
            case ExploreCategoryTypes::Songs:
                $request->merge(['include' => 'shows']);

                $showSongs = [
                    'showSongs' => [
                        'data' => MediaSongResource::collection($this->resource
                            ->exploreCategoryItems
                            ->pluck('model')
                        )->toArray($request),
                        'next' => empty($nextPageURL) ? null : $nextPageURL,
                    ]
                ];

                $request->merge(['include' => '']);

                return $showSongs;
            case ExploreCategoryTypes::UpcomingShows:
            case ExploreCategoryTypes::NewShows:
            case ExploreCategoryTypes::RecentlyUpdateShows:
            case ExploreCategoryTypes::RecentlyFinishedShows:
            case ExploreCategoryTypes::ContinuingShows:
            case ExploreCategoryTypes::ShowsSeason:
            case ExploreCategoryTypes::MostPopularShows:
            case ExploreCategoryTypes::Shows:
                return [
                    'shows' => [
                        'data' => AnimeResourceIdentity::collection($this->resource
                            ->exploreCategoryItems
                            ->pluck('model')
                        ),
                        'next' => empty($nextPageURL) ? null : $nextPageURL,
                    ]
                ];
            case ExploreCategoryTypes::Literatures:
                return [
                    'literatures' => [
                        'data' => LiteratureResourceIdentity::collection($this->resource
                            ->pluck('model')
                        ),
                        'next' => empty($nextPageURL) ? null : $nextPageURL,
                    ]
                ];
            case ExploreCategoryTypes::NewLiteratures:
            case ExploreCategoryTypes::RecentlyUpdateLiteratures:
            case ExploreCategoryTypes::RecentlyFinishedLiteratures:
            case ExploreCategoryTypes::ContinuingLiteratures:
            case ExploreCategoryTypes::LiteraturesSeason:
            case ExploreCategoryTypes::MostPopularLiteratures:
            case ExploreCategoryTypes::UpcomingLiteratures:
                return [
                    'literatures' => [
                        'data' => LiteratureResourceIdentity::collection($this->resource
                            ->exploreCategoryItems
                            ->pluck('model')
                        ),
                        'next' => empty($nextPageURL) ? null : $nextPageURL,
                    ]
                ];
            case ExploreCategoryTypes::Games:
                return [
                    'games' => [
                        'data' => GameResourceIdentity::collection($this->resource
                            ->pluck('model')
                        ),
                        'next' => empty($nextPageURL) ? null : $nextPageURL,
                    ]
                ];
            case ExploreCategoryTypes::NewGames:
            case ExploreCategoryTypes::RecentlyUpdateGames:
            case ExploreCategoryTypes::GamesSeason:
            case ExploreCategoryTypes::MostPopularGames:
            case ExploreCategoryTypes::UpcomingGames:
                return [
                    'games' => [
                        'data' => GameResourceIdentity::collection($this->resource
                            ->exploreCategoryItems
                            ->pluck('model')
                        ),
                        'next' => empty($nextPageURL) ? null : $nextPageURL,
                    ]
                ];
            case ExploreCategoryTypes::ReCAP:
                return [
                    'recaps' => [
                        'data' => RecapResource::collection($this->resource
                            ->exploreCategoryItems
                            ->pluck('model')
                        ),
                        'next' => empty($nextPageURL) ? null : $nextPageURL,
                    ]
                ];
            default: // Return empty type by default
                return [
                    $this->resource->type => null,
                    'next' => null,
                ];
        }
    }
}
