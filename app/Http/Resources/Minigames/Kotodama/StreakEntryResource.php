<?php

namespace App\Http\Resources\Minigames\Kotodama;

use App\Http\Resources\UserResource;
use App\Models\Minigames\Kotodama\UserStats;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StreakEntryResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var UserStats $resource
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
     * @param UserStats $resource
     * @param int       $rank
     */
    public function __construct(UserStats $resource, int $rank)
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
            'id' => (string) $this->resource->user_id,
            'type' => 'kotodama-streak-entries',
            'href' => route('api.users.profile', $this->resource->user, false),
            'attributes' => [
                'rank' => $this->rank,
                'currentStreak' => (int) $this->resource->current_streak,
                'maxStreak' => (int) $this->resource->max_streak,
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
