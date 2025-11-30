<?php

namespace App\Http\Resources;

use App\Models\Manga;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LiteratureResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Manga $resource
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
        $resource = LiteratureResourceBasic::make($this->resource)->toArray($request);

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
                    'related-literatures' => array_merge($relationships, $this->getRelatedMangasRelationship()),
                    'related-games' => array_merge($relationships, $this->getRelatedGamesRelationship()),
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
                'href' => route('api.manga.cast', $this->resource, false),
                'data' => LiteratureCastResourceIdentity::collection($this->resource->cast)
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
                'href' => route('api.manga.characters', $this->resource, false),
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
                'href' => route('api.manga.related-shows', $this->resource, false),
                'data' => MediaRelatedResource::collection($this->resource->animeRelations)
            ]
        ];
    }

    /**
     * Returns the related-shows relationship for the resource.
     *
     * @return array
     */
    protected function getRelatedMangasRelationship(): array
    {
        return [
            'relatedMangas' => [
                'href' => route('api.manga.related-literatures', $this->resource, false),
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
                'href' => route('api.manga.related-games', $this->resource, false),
                'data' => MediaRelatedResource::collection($this->resource->gameRelations)
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
                'href' => route('api.manga.staff', $this->resource, false),
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
                'href' => route('api.manga.studios', $this->resource, false),
                'data' => StudioResourceIdentity::collection($this->resource->studios)
            ]
        ];
    }
}
