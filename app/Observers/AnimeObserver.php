<?php

namespace App\Observers;

use App\Models\Anime;
use App\Services\AnimeTVRatingPropagator;
use Throwable;

class AnimeObserver
{
    /**
     * Handle the Anime "created" event.
     */
    public function created(Anime $anime): void
    {
        //
    }

    /**
     * Handle the Anime "updated" event.
     *
     * @throws Throwable
     */
    public function updated(Anime $anime): void
    {
        AnimeTVRatingPropagator::handle($anime);
    }

    /**
     * Handle the Anime "deleted" event.
     */
    public function deleted(Anime $anime): void
    {
        //
    }

    /**
     * Handle the Anime "restored" event.
     */
    public function restored(Anime $anime): void
    {
        //
    }

    /**
     * Handle the Anime "force deleted" event.
     */
    public function forceDeleted(Anime $anime): void
    {
        //
    }
}
