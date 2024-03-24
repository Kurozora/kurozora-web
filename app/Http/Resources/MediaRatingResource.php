<?php

namespace App\Http\Resources;

use App\Models\Anime;
use App\Models\Character;
use App\Models\Episode;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MediaRating;
use App\Models\Person;
use App\Models\Song;
use App\Models\Studio;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaRatingResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var MediaRating $resource
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
        $resource = MediaRatingResourceBasic::make($this->resource)->toArray($request);

        // Add relationships
        $relationships = [];

        // Add specific data per type
        $relationships = array_merge($relationships, $this->getTypeSpecificData($request));

        $relationships = array_merge($relationships, $this->getUserDetails());

        return array_merge($resource, ['relationships' => $relationships]);
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
        switch ($this->resource->model_type) {
            case Anime::class:
                return [
                    'shows' => [
                        'data' => AnimeResourceIdentity::collection([$this->resource->model_id])
                    ]
                ];
            case Character::class:
                return [
                    'characters' => [
                        'data' => CharacterResourceIdentity::collection([$this->resource->model_id])
                    ]
                ];
            case Episode::class:
                return [
                    'episodes' => [
                        'data' => EpisodeResourceIdentity::collection([$this->resource->model_id])
                    ]
                ];
            case Game::class:
                return [
                    'games' => [
                        'data' => GameResourceIdentity::collection([$this->resource->model_id])
                    ]
                ];
            case Manga::class:
                return [
                    'literatures' => [
                        'data' => LiteratureResourceIdentity::collection([$this->resource->model_id])
                    ]
                ];
            case Person::class:
                return [
                    'people' => [
                        'data' => PersonResourceIdentity::collection([$this->resource->model_id])
                    ]
                ];
            case Song::class:
                return [
                    'songs' => [
                        'data' => SongResourceIdentity::collection([$this->resource->model_id])
                    ]
                ];
            case Studio::class:
                return [
                    'studios' => [
                        'data' => StudioResourceIdentity::collection([$this->resource->model_id])
                    ]
                ];
            default:
                return [
                    'shows' => [
                        'data' => []
                    ]
                ];
        }
    }

    /**
     * Get the user details belonging to the feed message.
     *
     * @return array
     */
    private function getUserDetails(): array
    {
        $mediaRating = $this->resource;

        return [
            'users' => [
                'data' => UserResourceBasic::collection([$mediaRating->user]),
            ]
        ];
    }
}
