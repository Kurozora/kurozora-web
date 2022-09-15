<?php

namespace App\Events;

use App\Models\Episode;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EpisodeViewed
{
    use Dispatchable, SerializesModels;

    /**
     * @var Episode $episode
     */
    public Episode $episode;

    /**
     * Create a new event instance.
     *
     * @param Episode $episode
     */
    public function __construct(Episode $episode)
    {
        $this->episode = $episode;
    }
}
