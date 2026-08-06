<?php

namespace App\Http\Resources\Minigames\Kotodama;

use App\Http\Resources\UserResource;
use App\Models\Minigames\Kotodama\Game;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaderboardEntryResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Game $resource
     */
    public $resource;

    /**
     * The rank of this entry.
     *
     * @var int
     */
    protected int $rank;

    /**
     * Create a new resource instance.
     *
     * @param Game $resource
     * @param int  $rank
     */
    public function __construct(Game $resource, int $rank)
    {
        parent::__construct($resource);
        $this->rank = $rank;
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
            'type' => 'kotodama-leaderboard-entries',
            'href' => route('api.kotodama.games.details', $this->resource, false),
            'attributes' => [
                'rank' => $this->rank,
                'guessCount' => (int) $this->resource->guess_count,
                'durationMs' => $this->resource->duration_ms,
                'finishedAt' => $this->resource->finished_at?->timestamp,
            ],
            'relationships' => $this->getUserRelationship(),
        ];
    }

    /**
     * Returns the user relationship for the resource.
     *
     * @return array
     */
    protected function getUserRelationship(): array
    {
        return [
            'users' => [
                'data' => UserResource::collection([$this->resource->user]),
            ],
        ];
    }
}
