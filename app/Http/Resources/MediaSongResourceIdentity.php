<?php

namespace App\Http\Resources;

use App\Models\Game;
use App\Models\MediaSong;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaSongResourceIdentity extends JsonResource
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
     *
     * @return array
     */
    public function toArray(Request $request): array
    {
        $routeName = match ($this->resource->model->getMorphClass()) {
            Game::class => 'api.games.songs',
            default => 'api.anime.songs',
        };

        return [
            'id' => (string) $this->resource->id,
            'uuid' => (string) $this->resource->id, // TODO: - Remove after 1.9.0
            'type' => 'media-songs',
            'href' => route($routeName, $this->resource->model, false),
        ];
    }
}
