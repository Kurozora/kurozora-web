<?php

namespace App\Actions\Web\Profile;

use App\Contracts\Web\Profile\ImportsUserLibraryFromMAL;
use App\Enums\MALImportBehavior;
use App\Jobs\ProcessMALImport;
use App\Models\User;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ImportUserLibraryFromMAL implements ImportsUserLibraryFromMAL
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
    public function update(User $user, array $input)
    {
        Validator::make($input, [
            'mal_file'          => ['required', 'file', 'mimes:xml', 'max:' . config('mal-import.max_xml_file_size')],
            'import_behavior'   => ['required', 'integer', 'in:' . implode(',', MALImportBehavior::getValues())],
        ])->validateWithBag('importLibraryFromMAL');

        // Get the authenticated user
        if (!$user->canDoMALImport()) {
            $cooldownDays = config('mal-import.cooldown_in_days');

            Validator::make(['mal_file' => now()->subDays(config('mal-import.cooldown_in_days'))], [
                'mal_file' => ['after:' . $user->last_mal_import_at]
            ], [
                'after' => __('You can only perform a MAL import every :x day(s).', ['x' => $cooldownDays])
            ])->validateWithBag('importLibraryFromMAL');
        }

        // Read XML file
        $xmlContent = File::get($input['mal_file']->getRealPath());

        // Get import behavior
        $behavior = MALImportBehavior::fromValue((int) $input['import_behavior']);

        // Dispatch job
        dispatch(new ProcessMALImport($user, $xmlContent, $behavior));

        // Update last MAL import date for user
        $user->update([
            'last_mal_import_at' => now()
        ]);
    }
}
