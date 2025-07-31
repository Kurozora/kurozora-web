<?php

namespace App\Http\Resources;

use App\Models\GameCast;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameCastResourceIdentity extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var GameCast $resource
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
        return [
            'id' => (string) $this->resource->id,
            'type' => 'cast',
            'href' => route('api.game-cast.details', $this->resource, false),
        ];
    }
}
