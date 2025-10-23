<?php

namespace App\Http\Resources;

use App\Models\Game;
use App\Models\MediaSong;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaSongResourceBasic extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var MediaSong $resource
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
        $resource = MediaSongResourceIdentity::make($this->resource)->toArray($request);
        $resource = array_merge($resource, [
            'song'          => SongResourceBasic::make($this->resource->song),
            'attributes'    => [
                'type'      => $this->resource->type->value,
                'position'  => $this->resource->position,
                'episodes'  => $this->resource->episodes ?? '1',
            ]
        ]);

        $include = $request->input('include');

        if ($include) {
            if (is_string($include)) {
                $includes = array_unique(explode(',', $include));
            } else if (is_array($include)) {
                $includes = $include;
            } else {
                $includes = [];
            }

            foreach ($includes as $include) {
                switch ($include) {
                    case 'shows':
                        $resource = array_merge($resource, $this->getModelRelationship());
                        break;
                }
            }
        }

        return $resource;
    }

    /**
     * Returns the model relationship for the resource.
     *
     * @return array
     */
    protected function getModelRelationship(): array
    {
        return match ($this->resource->model_type) {
            Game::class => ['game' => GameResourceBasic::make($this->resource->model)],
            default => ['show' => AnimeResourceBasic::make($this->resource->model)]
        };
    }
}
