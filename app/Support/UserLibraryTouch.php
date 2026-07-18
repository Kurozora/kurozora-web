<?php

namespace App\Support;

use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\UserLibrary;

class UserLibraryTouch
{
    /**
     * Bumps `user_libraries.updated_at` for the user's in-library rows whose
     * morph identity matches one of the given trackable IDs.
     *
     * @param int            $userID
     * @param string         $morphClass
     * @param array<int>|int $trackableIDs
     * @return void
     */
    public static function touch(int $userID, string $morphClass, array|int $trackableIDs): void
    {
        if (!in_array($morphClass, [Anime::class, Manga::class, Game::class], true)) {
            return;
        }

        $ids = array_values(array_unique(array_filter((array) $trackableIDs, fn ($id) => $id !== null)));

        if (empty($ids)) {
            return;
        }

        UserLibrary::where('user_id', '=', $userID)
            ->where('trackable_type', '=', $morphClass)
            ->whereIn('trackable_id', $ids)
            ->update(['updated_at' => now()->format('Y-m-d H:i:s.u')]);
    }

    /**
     * Bumps `user_libraries.updated_at` for every in-library row of the given
     * morph type belonging to the user.
     *
     * @param int         $userID
     * @param string|null $morphClass When null, every in-library row is touched.
     * @return void
     */
    public static function touchAll(int $userID, ?string $morphClass = null): void
    {
        $query = UserLibrary::where('user_id', '=', $userID);

        if ($morphClass !== null) {
            if (!in_array($morphClass, [Anime::class, Manga::class, Game::class], true)) {
                return;
            }
            $query->where('trackable_type', '=', $morphClass);
        }

        $query->update(['updated_at' => now()->format('Y-m-d H:i:s.u')]);
    }
}
