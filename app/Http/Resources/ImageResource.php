<?php

namespace App\Http\Resources;

use App\Models\Anime;
use App\Models\Character;
use App\Models\Episode;
use App\Models\Game;
use App\Models\Manga;
use App\Models\Media;
use App\Models\Person;
use App\Models\Song;
use App\Models\Studio;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImageResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Media $resource
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
        $resource = MediaResource::make($this->resource)->toArray($request);

        $relationships = [];
        $relationships = match ($this->resource->model_type) {
            Anime::class => array_merge($relationships, $this->getAnimeRelationship()),
            Character::class => array_merge($relationships, $this->getCharactersRelationship()),
            Episode::class => array_merge($relationships, $this->getEpisodesRelationship()),
            Game::class => array_merge($relationships, $this->getGamesRelationship()),
            Manga::class => array_merge($relationships, $this->getMangaRelationship()),
            Person::class => array_merge($relationships, $this->getPeopleRelationship()),
            Song::class => array_merge($relationships, $this->getSongsRelationship()),
            Studio::class => array_merge($relationships, $this->getStudiosRelationship()),
            default => $relationships
        };

        $resource = array_merge($resource, ['relationships' => $relationships]);

        return $resource;
    }

    /**
     * Returns the anime relationship for the resource.
     *
     * @return array
     */
    protected function getAnimeRelationship(): array
    {
        return [
            'shows' => [
                'data' => AnimeResourceIdentity::collection([$this->resource->model])
            ]
        ];
    }

    /**
     * Returns the characters relationship for the resource.
     *
     * @return array
     */
    protected function getCharactersRelationship(): array
    {
        return [
            'characters' => [
                'data' => CharacterResourceIdentity::collection([$this->resource->model])
            ]
        ];
    }

    /**
     * Returns the episodes relationship for the resource.
     *
     * @return array
     */
    protected function getEpisodesRelationship(): array
    {
        return [
            'episodes' => [
                'data' => EpisodeResourceIdentity::collection([$this->resource->model])
            ]
        ];
    }

    /**
     * Returns the games relationship for the resource.
     *
     * @return array
     */
    protected function getGamesRelationship(): array
    {
        return [
            'games' => [
                'data' => GameResourceIdentity::collection([$this->resource->model])
            ]
        ];
    }

    /**
     * Returns the literatures relationship for the resource.
     *
     * @return array
     */
    protected function getMangaRelationship(): array
    {
        return [
            'literatures' => [
                'data' => LiteratureResourceIdentity::collection([$this->resource->model])
            ]
        ];
    }

    /**
     * Returns the people relationship for the resource.
     *
     * @return array
     */
    protected function getPeopleRelationship(): array
    {
        return [
            'people' => [
                'data' => PersonResourceIdentity::collection([$this->resource->model])
            ]
        ];
    }

    /**
     * Returns the seasons relationship for the resource.
     *
     * @return array
     */
    protected function getSongsRelationship(): array
    {
        return [
            'songs' => [
                'data' => SongResourceIdentity::collection([$this->resource->model])
            ]
        ];
    }

    /**
     * Returns the studios relationship for the resource.
     *
     * @return array
     */
    protected function getStudiosRelationship(): array
    {
        return [
            'studios' => [
                'data' => StudioResourceIdentity::collection([$this->resource->model])
            ]
        ];
    }
}
