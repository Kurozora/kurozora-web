<?php

namespace App\Events;

use App\Models\Season;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SeasonViewed
{
    use Dispatchable, SerializesModels;

    /**
     * @var Season $season
     */
    public Season $season;

    /**
     * Create a new event instance.
     *
     * @param Season $season
     */
    public function __construct(Season $season)
    {
        $this->season = $season;
    }
}
