<?php

namespace App\Http\Resources;

use App\Models\Manga;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MangaResource extends JsonResource
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
    public function toArray($request): array
    {
        $resource = MangaResourceBasic::make($this->resource)->toArray($request);

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
                    case 'related-mangas':
                        $relationships = array_merge($relationships, $this->getRelatedMangasRelationship());
                        break;
                    case 'staff':
                        $relationships = array_merge($relationships, $this->getStaffRelationship());
                        break;
                    case 'studios':
                        $request->merge(['include' => 'mangas']);
                        $request->merge(['manga' => $this->resource]);
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
                'data' => MangaCastResourceIdentity::collection($this->resource->getCast(Manga::MAXIMUM_RELATIONSHIPS_LIMIT))
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
                'data' => CharacterResourceBasic::collection($this->resource->getCharacters(Manga::MAXIMUM_RELATIONSHIPS_LIMIT))
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
                'data' => MediaRelatedShowResource::collection($this->resource->getMangaRelations(Manga::MAXIMUM_RELATIONSHIPS_LIMIT))
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
                'href' => route('api.manga.related-mangas', $this->resource, false),
                'data' => MediaRelatedShowResource::collection($this->resource->getMangaRelations(Manga::MAXIMUM_RELATIONSHIPS_LIMIT))
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
                'data' => MediaStaffResource::collection($this->resource->getMediaStaff(Manga::MAXIMUM_RELATIONSHIPS_LIMIT))
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
                'data' => StudioResourceIdentity::collection($this->resource->getStudios(Manga::MAXIMUM_RELATIONSHIPS_LIMIT))
            ]
        ];
    }
}
