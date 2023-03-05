<?php

namespace App\Http\Resources;

use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Game $resource
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
        $resource = GameResourceBasic::make($this->resource)->toArray($request);

        if ($request->input('include')) {
            $includes = array_unique(explode(',', $request->input('include')));

            $relationships = [];
            foreach ($includes as $include) {
                switch ($include) {
                    case 'cast':
                        $relationships = array_merge($relationships, $this->getCastRelationship());
                        break;
                    case 'characters':
                        $relationships = array_merge($relationships, $this->getCharactersRelationship());
                        break;
                    case 'related-shows':
                        $relationships = array_merge($relationships, $this->getRelatedShowsRelationship());
                        break;
                    case 'related-literatures':
                        $relationships = array_merge($relationships, $this->getRelatedLiteraturesRelationship());
                        break;
                    case 'related-games':
                        $relationships = array_merge($relationships, $this->getRelatedGamesRelationship());
                        break;
                    case 'seasons':
                        $relationships = array_merge($relationships, $this->getSeasonsRelationship());
                        break;
                    case 'songs':
                        $relationships = array_merge($relationships, $this->getSongsRelationship());
                        break;
                    case 'staff':
                        $relationships = array_merge($relationships, $this->getStaffRelationship());
                        break;
                    case 'studios':
                        $request->merge(['include' => 'shows']);
                        $request->merge(['anime' => $this->resource]);
                        $relationships = array_merge($relationships, $this->getStudiosRelationship());
                        break;
                }
            }

            $resource = array_merge($resource, ['relationships' => $relationships]);
        }

        return $resource;
    }

    /**
     * Returns the cast relationship for the resource.
     *
     * @return array
     */
    protected function getCastRelationship(): array
    {
        return [
            'cast' => [
                'href' => route('api.games.cast', $this->resource, false),
                'data' => GameCastResourceIdentity::collection($this->resource->getCast(Game::MAXIMUM_RELATIONSHIPS_LIMIT))
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
                'href' => route('api.games.characters', $this->resource, false),
                'data' => CharacterResourceBasic::collection($this->resource->getCharacters(Game::MAXIMUM_RELATIONSHIPS_LIMIT))
            ]
        ];
    }

    /**
     * Returns the related-shows relationship for the resource.
     *
     * @return array
     */
    protected function getRelatedShowsRelationship(): array
    {
        return [
            'relatedShows' => [
                'href' => route('api.games.related-shows', $this->resource, false),
                'data' => MediaRelatedResource::collection($this->resource->getAnimeRelations(Game::MAXIMUM_RELATIONSHIPS_LIMIT))
            ]
        ];
    }

    /**
     * Returns the related-literatures relationship for the resource.
     *
     * @return array
     */
    protected function getRelatedLiteraturesRelationship(): array
    {
        return [
            'relatedLiteratures' => [
                'href' => route('api.games.related-literatures', $this->resource, false),
                'data' => MediaRelatedResource::collection($this->resource->getMangaRelations(Game::MAXIMUM_RELATIONSHIPS_LIMIT))
            ]
        ];
    }

    /**
     * Returns the related-literatures relationship for the resource.
     *
     * @return array
     */
    protected function getRelatedGamesRelationship(): array
    {
        return [
            'relatedGames' => [
                'href' => route('api.games.related-games', $this->resource, false),
                'data' => MediaRelatedResource::collection($this->resource->getGameRelations(Game::MAXIMUM_RELATIONSHIPS_LIMIT))
            ]
        ];
    }

    /**
     * Returns the seasons relationship for the resource.
     *
     * @return array
     */
    protected function getSeasonsRelationship(): array
    {
        return [
            'seasons' => [
                'href' => route('api.games.seasons', $this->resource, false),
                'data' => SeasonResourceIdentity::collection($this->resource->getSeasons(Game::MAXIMUM_RELATIONSHIPS_LIMIT, reversed: true))
            ]
        ];
    }

    /**
     * Returns the songs relationship for the resource.
     *
     * @return array
     */
    protected function getSongsRelationship(): array
    {
        return [
            'showSongs' => [
                'href' => route('api.games.songs', $this->resource, false),
                'data' => MediaSongResource::collection($this->resource->getMediaSongs(Game::MAXIMUM_RELATIONSHIPS_LIMIT))
            ]
        ];
    }

    /**
     * Returns the people relationship for the resource.
     *
     * @return array
     */
    protected function getStaffRelationship(): array
    {
        return [
            'staff' => [
                'href' => route('api.games.staff', $this->resource, false),
                'data' => MediaStaffResource::collection($this->resource->getMediaStaff(Game::MAXIMUM_RELATIONSHIPS_LIMIT))
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
                'href' => route('api.games.studios', $this->resource, false),
                'data' => StudioResourceIdentity::collection($this->resource->getStudios(Game::MAXIMUM_RELATIONSHIPS_LIMIT))
            ]
        ];
    }
}
