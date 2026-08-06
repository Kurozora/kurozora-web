<?php

namespace App\Http\Resources\Minigames\Kotodama;

use App\Models\Minigames\Kotodama\DailyPuzzle;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyPuzzleResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var DailyPuzzle $resource
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
            'type' => 'kotodama-daily-puzzles',
            'href' => route('api.kotodama.archive', ['date' => $this->resource->puzzle_date?->toDateString()], false),
            'attributes' => [
                'puzzleNumber' => (int) $this->resource->puzzle_number,
                'puzzleDate' => $this->resource->puzzle_date?->toDateString(),
                'nextPuzzleAt' => $this->resource->puzzle_date?->copy()->addDay()->startOfDay()->timestamp,
            ],
        ];
    }
}
