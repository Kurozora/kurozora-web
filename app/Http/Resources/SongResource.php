<?php

namespace App\Http\Resources;

use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SongResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Song $resource
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
        $resource = SongResourceBasic::make($this->resource)->toArray($request);

        if ($request->input('include')) {
            $includes = array_unique(explode(',', $request->input('include')));

            $relationships = [];
            foreach ($includes as $include) {
                switch ($include) {
                    case 'shows':
                        $relationships = array_merge($relationships, $this->getAnimeRelationship());
                        break;
                }
            }

            $resource = array_merge($resource, ['relationships' => $relationships]);
        }

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
                'href' => route('api.songs.anime', $this->resource, false),
                'data' => AnimeResourceBasic::collection($this->resource->anime)
            ]
        ];
    }
}
