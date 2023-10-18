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
                        $relationships = array_merge($relationships, $this->getRelatedMangasRelationship());
                        break;
                    case 'related-games':
                        $relationships = array_merge($relationships, $this->getRelatedGamesRelationship());
                        break;
                    case 'staff':
                        $relationships = array_merge($relationships, $this->getStaffRelationship());
                        break;
                    case 'studios':
                        $request->merge(['include' => 'mangas']);
                        $request->merge(['literature' => $this->resource]);
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
                'href' => route('api.manga.cast', $this->resource, false),
                'data' => MangaCastResourceIdentity::collection($this->resource->cast)
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
