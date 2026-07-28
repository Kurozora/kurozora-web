<?php

namespace App\Http\Resources\Minigames\Kotodama;

use App\Enums\MediaCollection;
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
     * @param int        $rank
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
        $user = $this->resource->user;

        return [
            'id' => (string) $this->resource->id,
            'type' => 'kotodama-leaderboard-entries',
            'attributes' => [
                'rank' => $this->rank,
                'guessCount' => (int) $this->resource->guess_count,
                'durationMs' => $this->resource->duration_ms,
                'finishedAt' => $this->resource->finished_at?->timestamp,
                'user' => [
                    'id' => (string) $user?->id,
                    'username' => $user?->username,
                    'slug' => $user?->slug,
                    'profileImageUrl' => $user?->getFirstMediaFullUrl(MediaCollection::Profile()) ?: null,
                ],
            ],
        ];
    }
}
