<?php

namespace App\Console\Commands\Fixers;

use App\Models\Studio;
use DB;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class StudioTVRating extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:studio_tv_rating';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix studios TV rating and NSFW status based on associated media.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Studio::withoutGlobalScopes()
            ->withMin([
                'anime as min_anime_tv_rating_id' => function ($query) {
                    $query->withoutGlobalScopes()->where('tv_rating_id', '!=', 1);
                },
            ], 'tv_rating_id')
            ->withMin([
                'manga as min_manga_tv_rating_id' => function ($query) {
                    $query->withoutGlobalScopes()->where('tv_rating_id', '!=', 1);
                },
            ], 'tv_rating_id')
            ->withMin([
                'games as min_game_tv_rating_id' => function ($query) {
                    $query->withoutGlobalScopes()->where('tv_rating_id', '!=', 1);
                },
            ], 'tv_rating_id')
            ->withExists([
                'anime as anime_has_nsfw' => function ($query) {
                    $query->withoutGlobalScopes()->where('is_nsfw', '=', true);
                },
                'manga as manga_has_nsfw' => function ($query) {
                    $query->withoutGlobalScopes()->where('is_nsfw', '=', true);
                },
                'games as game_has_nsfw' => function ($query) {
                    $query->withoutGlobalScopes()->where('is_nsfw', '=', true);
                },
            ])
            ->where('tv_rating_id', '=', null)
            ->with(['mediaStat', 'tv_rating', 'predecessors', 'successor'])
            ->chunkById(1000, function (Collection $studios) {
                /** @var Studio $studio */
                foreach ($studios as $studio) {
                    // Determine tv_rating_id: smallest non-null min_* across relations
                    $candidates = [
                        $studio->min_anime_tv_rating_id,
                        $studio->min_manga_tv_rating_id,
                        $studio->min_game_tv_rating_id,
                    ];

                    // Filter nulls and cast to ints
                    $candidates = array_values(array_filter($candidates, function ($tvRatingID) {
                        return $tvRatingID !== null && $tvRatingID !== '';
                    }));

                    $newTvRatingId = null;
                    if (!empty($candidates)) {
                        // All values should be integers
                        $newTvRatingId = (int) min($candidates);
                    }

                    // Determine is_nsfw based on ANY related media flagged is_nsfw
                    $newIsNsfw = (
                        ($studio->anime_has_nsfw ?? false) ||
                        ($studio->manga_has_nsfw ?? false) ||
                        ($studio->game_has_nsfw ?? false)
                    );

                    // Compare with current values; update only if changed.
                    $needsUpdate = false;
                    $updates = [];

                    // Normalize current tv_rating_id to null/int
                    $currentTvRatingId = $studio->tv_rating_id === null ? null : (int) $studio->tv_rating_id;

                    if ($newTvRatingId !== null && $currentTvRatingId !== $newTvRatingId) {
                        $updates['tv_rating_id'] = $newTvRatingId;
                        $needsUpdate = true;
                    } elseif ($newTvRatingId === null && $currentTvRatingId !== null) {
                        // If no non-1 rating exists anymore, set to default 1
                        if ($currentTvRatingId !== 1) {
                            $updates['tv_rating_id'] = 1;
                            $needsUpdate = true;
                        }
                    } else if ($newTvRatingId === null && $currentTvRatingId === null) {
                        $updates['tv_rating_id'] = 1;
                        $needsUpdate = true;
                    }

                    // Compare is_nsfw
                    $currentIsNsfw = (bool) $studio->is_nsfw;
                    if ($currentIsNsfw !== $newIsNsfw) {
                        $updates['is_nsfw'] = $newIsNsfw;
                        $needsUpdate = true;
                    }

                    if (!$needsUpdate) {
                        // Nothing to do for this studio
                        continue;
                    }

                    // Do update in transaction to be safe
                    DB::transaction(function () use ($studio, $updates, $newTvRatingId, $newIsNsfw) {
                        $studio->update($updates);
                    });

                    $this->info(sprintf(
                        'Updated Studio(id=%d, name="%s"): is_nsfw: %s -> %s, tv_rating_id: %s',
                        $studio->id,
                        $studio->name,
                        $studio->is_nsfw ? 'true' : 'false',
                        $updates['is_nsfw'] ?? ($studio->is_nsfw ? 'true' : 'false'),
                        $updates['tv_rating_id'] ?? $studio->tv_rating_id
                    ));
                }
            });

        return Command::SUCCESS;
    }
}
