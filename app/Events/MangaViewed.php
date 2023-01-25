<?php

namespace App\Events;

use App\Models\Manga;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MangaViewed
{
    use Dispatchable, SerializesModels;

    /**
     * @var Manga $manga
     */
    public Manga $manga;

    /**
     * Create a new event instance.
     *
     * @param Manga $manga
     */
    public function __construct(Manga $manga)
    {
        $this->manga = $manga;
    }
}
