<?php

namespace App\Listeners;

use App\Events\ModelViewed;
use App\Models\View;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class ModelViewedListener implements ShouldQueue
{
    use Queueable;

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
     *
     * @return void
     */
    public function handle(ModelViewed $event): void
    {
        if ($ip = $event->ip) {
            $modelID = $event->model->id;
            $class = $event->model->getMorphClass();

            View::insertUsing(
                ['viewable_id', 'viewable_type', 'ip_address', 'created_at', 'updated_at'],
                // Since selecting from `views` doesn't have a restrictive `WHERE`, it runs once per row in `views`.
                // The fix is to select from a dummy source (`SELECT 1`) instead of the same table, so it inserts at most one row.
                DB::table(DB::raw('(SELECT 1 AS dummy) AS src'))
                    ->selectRaw('? AS viewable_id, ? AS viewable_type, ? AS ip_address, NOW() AS created_at, NOW() AS updated_at', [
                        $modelID,
                        $class,
                        $ip,
                    ])
                    ->whereNotExists(function ($sub) use ($modelID, $class, $ip) {
                        $sub->from('views')
                            ->where('viewable_id', $modelID)
                            ->where('viewable_type', $class)
                            ->where('ip_address', $ip)
                            ->where('created_at', '>=', DB::raw('NOW() - INTERVAL 1 DAY')); //
                    })
            );
        }
    }
}
