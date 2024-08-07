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

        if ($includeInput = $request->input('include')) {
            // Include relation propagates to nested Resource objects.
            // To avoid loading unnecessary relations, we set it to
            // an empty value.
            $request->merge(['include' => '']);
            if (is_string($includeInput)) {
                $includeInput = explode(',', $includeInput);
            }
            $includes = array_unique($includeInput);

            $relationships = [];
            foreach ($includes as $include) {
                $relationships = match ($include) {
                    'cast' => array_merge($relationships, $this->getCastRelationship()),
                    'characters' => array_merge($relationships, $this->getCharactersRelationship()),
                    'related-shows' => array_merge($relationships, $this->getRelatedShowsRelationship()),
                    'related-literatures' => array_merge($relationships, $this->getRelatedLiteraturesRelationship()),
                    'related-games' => array_merge($relationships, $this->getRelatedGamesRelationship()),
                    'seasons' => array_merge($relationships, $this->getSeasonsRelationship()),
                    'songs' => array_merge($relationships, $this->getSongsRelationship()),
                    'staff' => array_merge($relationships, $this->getStaffRelationship()),
                    'studios' => array_merge($relationships, $this->getStudiosRelationship()),
                    default => $relationships
                };
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
                'data' => SeasonResourceIdentity::collection($this->resource->seasons)
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
