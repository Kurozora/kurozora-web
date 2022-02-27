<?php

namespace App\Http\Resources;

use App\Models\AnimeSong;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnimeSongResourceBasic extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var AnimeSong $resource
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
        $resource = AnimeSongResourceIdentity::make($this->resource)->toArray($request);
        $resource = array_merge($resource, [
            'song'          => SongResourceBasic::make($this->resource->song),
            'attributes'    => [
                'type'      => $this->resource->type->value,
                'position'  => $this->resource->position,
                'episodes'  => $this->resource->episodes,
            ]
        ]);

        if ($request->input('include')) {
            $includes = array_unique(explode(',', $request->input('include')));

            foreach ($includes as $include) {
                switch ($include) {
                    case 'shows':
                        $resource = array_merge($resource, $this->getAnimeRelationship());
                        break;
                }
            }
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
            'show' => AnimeResourceBasic::make($this->resource->anime)
        ];
    }
}
