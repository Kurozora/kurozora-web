<?php

namespace App\Http\Resources\Minigames\Kotodama;

use App\Models\Minigames\Kotodama\Game;
use App\Services\Minigames\Kotodama\ShareGridFormatter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShareGridResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Game $resource
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
            'type' => 'kotodama-share-grids',
            'href' => route('api.kotodama.games.share', $this->resource, false),
            'attributes' => [
                'text' => ShareGridFormatter::format($this->resource),
            ],
        ];
    }
}
