<?php

namespace App\Events;

use App\Models\Manga;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BareBonesMangaAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The MAL id of the anime.
     *
     * @var string $malID
     */
    public string $malID;

    /**
     * Create a new event instance.
     *
     * @param Manga $manga
     */
    public function __construct(Manga $manga)
    {
        $this->malID = $manga->mal_id;
    }
}
