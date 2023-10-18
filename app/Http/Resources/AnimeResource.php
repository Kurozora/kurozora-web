<?php

namespace App\Http\Resources;

use App\Models\Anime;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnimeResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Anime $resource
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
        $resource = AnimeResourceBasic::make($this->resource)->toArray($request);

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
                'href' => route('api.anime.cast', $this->resource, false),
                'data' => ShowCastResourceIdentity::collection($this->resource->cast)
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
                'href' => route('api.anime.characters', $this->resource, false),
                'data' => CharacterResourceBasic::collection($this->resource->characters)
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
                'href' => route('api.anime.related-shows', $this->resource, false),
                'data' => MediaRelatedResource::collection($this->resource->animeRelations)
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
                'href' => route('api.anime.related-literatures', $this->resource, false),
                'data' => MediaRelatedResource::collection($this->resource->mangaRelations)
            ]
        ];
    }

    /**
     * Returns the related-games relationship for the resource.
     *
     * @return array
     */
    protected function getRelatedGamesRelationship(): array
    {
        return [
            'relatedGames' => [
                'href' => route('api.anime.related-games', $this->resource, false),
                'data' => MediaRelatedResource::collection($this->resource->gameRelations)
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
                'href' => route('api.anime.seasons', $this->resource, false),
                'data' => SeasonResourceIdentity::collection($this->resource->getSeasons(Anime::MAXIMUM_RELATIONSHIPS_LIMIT, reversed: true))
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
                'href' => route('api.anime.songs', $this->resource, false),
                'data' => MediaSongResource::collection($this->resource->mediaSongs)
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
                'href' => route('api.anime.staff', $this->resource, false),
                'data' => MediaStaffResource::collection($this->resource->mediaStaff)
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
                'href' => route('api.anime.studios', $this->resource, false),
                'data' => StudioResourceIdentity::collection($this->resource->studios)
            ]
        ];
    }
}
