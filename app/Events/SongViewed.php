<?php

namespace App\Events;

use App\Models\Song;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SongViewed
{
    use Dispatchable, SerializesModels;

    /**
     * @var Song $song
     */
    public Song $song;

    /**
     * Create a new event instance.
     *
     * @param Song $song
     */
    public function __construct(Song $song)
    {
        $this->song = $song;
    }
}
