<?php

namespace App\Http\Resources;

use App\Models\Anime;
use App\Models\Character;
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
        $resource = MediaRatingResourceIdentity::make($this->resource)->toArray($request);
        $resource = array_merge($resource, [
            'attributes' => [
                'score' => $this->resource->rating,
                'description' => $this->resource->description,
                'createdAt' => $this->resource->created_at->timestamp
            ]
        ]);

        // Add relationships
        $relationships = [];

        if ($this->resource->relationLoaded('model')) {
            // Add specific data per type
            $relationships = array_merge($relationships, $this->getTypeSpecificData($request));
        }

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
                        'data' => AnimeResourceBasic::collection([$this->resource->model])
                    ]
                ];
            case Character::class:
                return [
                    'characters' => [
                        'data' => CharacterResourceBasic::collection([$this->resource->model])
                    ]
                ];
            case Game::class:
                return [
                    'ames' => [
                        'data' => GameResourceBasic::collection([$this->resource->model])
                    ]
                ];
            case Manga::class:
                return [
                    'literatures' => [
                        'data' => LiteratureResourceBasic::collection([$this->resource->model])
                    ]
                ];
            case Person::class:
                return [
                    'people' => [
                        'data' => PersonResourceBasic::collection([$this->resource->model])
                    ]
                ];
            case Song::class:
                return [
                    'songs' => [
                        'data' => SongResourceBasic::collection([$this->resource->model])
                    ]
                ];
            case Studio::class:
                return [
                    'studios' => [
                        'data' => StudioResourceBasic::collection([$this->resource->model])
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
