<?php

namespace App\Actions\Web\Profile;

use App\Contracts\Web\Profile\ImportsUserAnimeLibrary;
use App\Enums\ImportBehavior;
use App\Enums\ImportService;
use App\Jobs\ProcessMALImport;
use App\Models\User;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ImportUserAnimeLibrary implements ImportsUserAnimeLibrary
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
            'import_service'    => ['required', 'integer', 'in:' . implode(',', ImportService::getValues())],
            'anime_file'        => ['required', 'file', 'mimes:xml', 'max:' . config('import.max_xml_file_size')],
            'import_behavior'   => ['required', 'integer', 'in:' . implode(',', ImportBehavior::getValues())],
        ])->validateWithBag('importAnimeLibrary');

        // Get the authenticated user
        if (!$user->canDoAnimeImport()) {
            $cooldownDays = config('import.cooldown_in_days');

            Validator::make(['anime_file' => now()->subDays(config('import.cooldown_in_days'))], [
                'anime_file' => ['after:' . $user->last_anime_import_at]
            ], [
                'after' => __('You can only perform an anime import every :x day(s).', ['x' => $cooldownDays])
            ])->validateWithBag('importAnimeLibrary');
        }

        // Read XML file
        $xmlContent = File::get($input['anime_file']->getRealPath());

        // Get the import service
        $importService = ImportService::fromValue((int) $input['import_service']);

        // Get import behavior
        $importBehavior = ImportBehavior::fromValue((int) $input['import_behavior']);

        // Dispatch job
        switch ($importService->value) {
            case ImportService::MAL:
            case ImportService::Kitsu:
                dispatch(new ProcessMALImport($user, $xmlContent, $importService, $importBehavior));
                break;
        }

        // Update last MAL import date for user
        $user->update([
            'last_anime_import_at' => now()
        ]);
    }
}
