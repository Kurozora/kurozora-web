<?php

namespace App\Events;

use App\Models\Studio;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudioViewed
{
    use Dispatchable, SerializesModels;

    /**
     * @var Studio $studio
     */
    public Studio $studio;

    /**
     * Create a new event instance.
     *
     * @param Studio $studio
     */
    public function __construct(Studio $studio)
    {
        $this->studio = $studio;
    }
}
