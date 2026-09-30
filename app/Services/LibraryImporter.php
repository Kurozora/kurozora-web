<?php

namespace App\Services;

use App\Enums\ImportBehavior;
use App\Enums\ImportService;
use App\Enums\UserLibraryKind;
use App\Jobs\ProcessMALImport;
use App\Models\User;

class LibraryImporter
{
    /**
     * Returns why the user can't import the given entries yet.
     *
     * @param User  $user
     * @param array $entriesByKind
     *
     * @return null|string
     */
    public static function cooldownMessage(User $user, array $entriesByKind): ?string
    {
        $cooldownDays = config('import.cooldown_in_days');

        foreach (array_keys($entriesByKind) as $kindValue) {
            if ($kindValue === UserLibraryKind::Manga && !$user->canDoMangaImport()) {
                return __('You can only perform a manga import every :x day(s).', ['x' => $cooldownDays]);
            }

            if ($kindValue === UserLibraryKind::Anime && !$user->canDoAnimeImport()) {
                return __('You can only perform an anime import every :x day(s).', ['x' => $cooldownDays]);
            }
        }

        return null;
    }

    /**
     * Queues the import of the given entries into the user's libraries.
     *
     * @param User           $user
     * @param array          $entriesByKind
     * @param ImportService  $service
     * @param ImportBehavior $behavior
     *
     * @return void
     */
    public static function dispatch(User $user, array $entriesByKind, ImportService $service, ImportBehavior $behavior): void
    {
        $importedAtAttributes = [];

        foreach ($entriesByKind as $kindValue => $entries) {
            dispatch(new ProcessMALImport($user, $entries, UserLibraryKind::fromValue($kindValue), $service, $behavior));

            $importedAtAttributes[$kindValue === UserLibraryKind::Manga ? 'manga_imported_at' : 'anime_imported_at'] = now();
        }

        $user->update($importedAtAttributes);
    }
}
