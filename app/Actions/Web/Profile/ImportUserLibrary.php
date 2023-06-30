<?php

namespace App\Actions\Web\Profile;

use App\Contracts\Web\Profile\ImportsUserLibrary;
use App\Enums\ImportBehavior;
use App\Enums\ImportService;
use App\Enums\UserLibraryKind;
use App\Jobs\ProcessMALImport;
use App\Models\User;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ImportUserLibrary implements ImportsUserLibrary
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param User $user
     * @param array $input
     *
     * @return void
     * @throws ValidationException
     * @throws FileNotFoundException
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'library' => ['required', 'integer', 'in:' . implode(',', UserLibraryKind::getValues())],
            'import_service' => ['required', 'integer', 'in:' . implode(',', ImportService::getValues())],
            'import_behavior' => ['required', 'integer', 'in:' . implode(',', ImportBehavior::getValues())],
            'library_file' => ['required', 'file', 'mimes:xml', 'max:' . config('import.max_xml_file_size')],
        ])->validateWithBag('importUserLibrary');

        // Get the library type
        $libraryKind = UserLibraryKind::fromValue((int) $input['library']);

        // Get whether user is in import cooldown period
        $isInImportCooldown = match ($libraryKind->value) {
            UserLibraryKind::Manga => !$user->canDoMangaImport(),
            default => !$user->canDoAnimeImport()
        };

        if ($isInImportCooldown) {
            $cooldownDays = config('import.cooldown_in_days');
            $lastImportDate = match ($libraryKind->value) {
                UserLibraryKind::Manga => $user->manga_imported_at,
                default => $user->anime_imported_at
            };

            $errorMessage = match ($libraryKind->value) {
                UserLibraryKind::Manga => __('You can only perform a manga import every :x day(s).', ['x' => $cooldownDays]),
                UserLibraryKind::Game => __('You can only perform a game import every :x day(s).', ['x' => $cooldownDays]),
                default => __('You can only perform an anime import every :x day(s).', ['x' => $cooldownDays])
            };

            Validator::make(['library_file' => now()->subDays(config('import.cooldown_in_days'))], [
                'library_file' => ['after:' . $lastImportDate]
            ], [
                'after' => $errorMessage
            ])->validateWithBag('importUserLibrary');
        }

        // Read XML file
        $xmlContent = File::get($input['library_file']->getRealPath());

        // Get the import service
        $importService = ImportService::fromValue((int) $input['import_service']);

        // Get import behavior
        $importBehavior = ImportBehavior::fromValue((int) $input['import_behavior']);

        // Dispatch job
        switch ($importService->value) {
            case ImportService::MAL:
            case ImportService::Kitsu:
                dispatch(new ProcessMALImport($user, $xmlContent, $libraryKind, $importService, $importBehavior));
                break;
        }

        // Update last library import date for user
        $lastImportDateKey = match ($libraryKind->value) {
            UserLibraryKind::Manga => 'manga_imported_at',
            default => 'anime_imported_at'
        };

        $user->update([
            $lastImportDateKey => now()
        ]);
    }
}
