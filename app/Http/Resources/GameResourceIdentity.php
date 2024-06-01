<?php

namespace App\Http\Resources;

use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResourceIdentity extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Game|string $resource
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
        return [
            'id'    => (string) ($this->resource?->id ?? $this->resource),
            'type'  => 'games',
            'href'  => route('api.games.view', $this->resource, false),
        ];
    }
}
