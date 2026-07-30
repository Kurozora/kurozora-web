<?php

namespace App\Http\Resources\Minigames\Kotodama;

use App\Models\Minigames\Kotodama\DailyPuzzle;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArchiveEntryResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var DailyPuzzle $resource
     */
    public $resource;

    /**
     * Whether the current user has solved this puzzle.
     *
     * @var bool
     */
    protected bool $isSolved;

    /**
     * Whether the current user has finished a game for this puzzle.
     *
     * @var bool
     */
    protected bool $isFinished;

    /**
     * Create a new resource instance.
     *
     * @param DailyPuzzle $resource
     * @param bool        $isSolved
     * @param bool        $isFinished
     */
    public function __construct(DailyPuzzle $resource, bool $isSolved, bool $isFinished)
    {
        parent::__construct($resource);
        $this->isSolved = $isSolved;
        $this->isFinished = $isFinished;
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
            'type' => 'kotodama-archive-entries',
            'href' => route('api.kotodama.archive', ['date' => $this->resource->puzzle_date?->toDateString()], false),
            'attributes' => [
                'puzzleNumber' => (int) $this->resource->puzzle_number,
                'puzzleDate' => $this->resource->puzzle_date?->toDateString(),
                'isSolved' => $this->isSolved,
                'isFinished' => $this->isFinished,
            ],
        ];
    }
}
