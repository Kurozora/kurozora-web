<?php

namespace App\Events;

use App\Models\Anime;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class AnimeViewed
{
    use Dispatchable, SerializesModels;

    public $anime;

    /**
     * Create a new event instance.
     *
     * @param Anime $anime
     */
    public function __construct(Anime $anime)
    {
        $this->anime = $anime;
    }
}
