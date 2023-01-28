<?php

namespace App\Actions\Web\Profile;

use App\Contracts\Web\Profile\ImportsUserLibrary;
use App\Enums\ImportBehavior;
use App\Enums\ImportService;
use App\Enums\UserLibraryType;
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
            'library_type'      => ['required', 'integer', 'in:' . implode(',', UserLibraryType::getValues())],
            'import_service'    => ['required', 'integer', 'in:' . implode(',', ImportService::getValues())],
            'import_behavior'   => ['required', 'integer', 'in:' . implode(',', ImportBehavior::getValues())],
            'library_file'      => ['required', 'file', 'mimes:xml', 'max:' . config('import.max_xml_file_size')],
        ])->validateWithBag('importUserLibrary');

        // Get the library type
        $libraryType = UserLibraryType::fromValue((int) $input['library_type']);

        // Get whether user is in import cooldown period
        $isInImportCooldown = match ($libraryType->value) {
            UserLibraryType::Manga => !$user->canDoMangaImport(),
            default => !$user->canDoAnimeImport()
        };

        if ($isInImportCooldown) {
            $cooldownDays = config('import.cooldown_in_days');
            $lastImportDate = match ($libraryType->value) {
                UserLibraryType::Manga => $user->last_manga_import_at,
                default => $user->last_anime_import_at
            };

            $errorMessage = match ($libraryType->value) {
                UserLibraryType::Manga => __('You can only perform a manga import every :x day(s).', ['x' => $cooldownDays]),
                UserLibraryType::Game => __('You can only perform a game import every :x day(s).', ['x' => $cooldownDays]),
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
                dispatch(new ProcessMALImport($user, $xmlContent, $libraryType, $importService, $importBehavior));
                break;
        }

        // Update last library import date for user
        $lastImportDateKey = match ($libraryType->value) {
            UserLibraryType::Manga => 'last_manga_import_at',
            default => 'last_anime_import_at'
        };

        $user->update([
            $lastImportDateKey => now()
        ]);
    }
}
