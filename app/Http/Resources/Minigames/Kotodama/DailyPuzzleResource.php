<?php

namespace App\Http\Resources\Minigames\Kotodama;

use App\Models\Minigames\Kotodama\DailyPuzzle;
use App\Models\Minigames\Kotodama\Game;
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
     * The current user's game for this puzzle.
     *
     * @var Game|null
     */
    protected ?Game $game;

    /**
     * Create a new resource instance.
     *
     * @param DailyPuzzle $resource
     * @param Game|null   $game
     */
    public function __construct(DailyPuzzle $resource, ?Game $game = null)
    {
        parent::__construct($resource);
        $this->game = $game;
    }

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
            'attributes' => [
                'puzzleNumber' => (int) $this->resource->puzzle_number,
                'puzzleDate' => $this->resource->puzzle_date?->toDateString(),
                'word' => WordResource::make($this->resource->word, $this->game),
            ],
        ];
    }
}
