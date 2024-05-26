<?php

namespace App\Listeners;

use App\Events\ModelViewed;
use App\Models\View;
use Illuminate\Contracts\Queue\ShouldQueue;
use RateLimiter;

class ModelViewedListener implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param ModelViewed $event
     * @return void
     */
    public function handle(ModelViewed $event): void
    {
        if ($ip = $event->ip) {
            $modelID = $event->model->id;
            $class = $event->model->getMorphClass();

            RateLimiter::attempt($ip . ':view-'. $class . ':' . $modelID , 1, function () use ($class, $modelID, $event) {
                View::create([
                    'viewable_id' => $modelID,
                    'viewable_type' => $class,
                ]);
            }, 60 * 60 * 24);
        }
    }
}
