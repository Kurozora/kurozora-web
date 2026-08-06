<?php

namespace App\Http\Resources\Minigames\Kotodama;

use App\Models\Minigames\Kotodama\Guess;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuessResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Guess $resource
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
            'position' => (int) $this->resource->position,
            'guess' => $this->resource->guess,
            'feedback' => $this->resource->feedback,
            'createdAt' => $this->resource->created_at?->timestamp,
        ];
    }
}
