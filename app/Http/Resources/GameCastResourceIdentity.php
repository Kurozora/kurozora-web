<?php

namespace App\Http\Resources;

use App\Models\GameCast;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

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
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray(Request $request): array|JsonSerializable|Arrayable
    {
        return [
            'id' => (string) $this->resource->id,
            'type' => 'cast',
            'href' => route('api.game-cast.details', $this->resource, false),
        ];
    }
}
