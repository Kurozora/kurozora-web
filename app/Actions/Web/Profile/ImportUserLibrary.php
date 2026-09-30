<?php

namespace App\Actions\Web\Profile;

use App\Contracts\Web\Profile\ImportsUserLibrary;
use App\Enums\ImportBehavior;
use App\Enums\ImportService;
use App\Enums\UserLibraryKind;
use App\Exceptions\UnsupportedLibraryExportException;
use App\Models\User;
use App\Services\LibraryImporter;
use App\Services\LibraryImportParser;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ImportUserLibrary implements ImportsUserLibrary
{
    /**
     * Validate and import the given user's library export.
     *
     * @param User $user
     * @param array $input
     *
     * @return void
     * @throws ValidationException
     */
    public function update(User $user, array $input): void
    {
        $importService = ImportService::coerce((int) ($input['import_service'] ?? -1));

        Validator::make($input, [
            'library' => [Rule::requiredIf(!$importService?->canInferLibraryKind()), 'nullable', 'integer', 'in:' . implode(',', UserLibraryKind::getValues())],
            'import_service' => ['required', 'integer', 'in:' . implode(',', ImportService::getValues())],
            'import_behavior' => ['required', 'integer', 'in:' . implode(',', ImportBehavior::getValues())],
            'library_file' => ['required', 'file', 'mimes:xml,gz,zip', 'max:' . config('import.max_xml_file_size')],
        ])->validateWithBag('importUserLibrary');

        try {
            $entriesByKind = LibraryImportParser::parseFile($input['library_file']);
        } catch (UnsupportedLibraryExportException $exception) {
            throw ValidationException::withMessages(['library_file' => $exception->getMessage()])
                ->errorBag('importUserLibrary');
        }

        $cooldownMessage = LibraryImporter::cooldownMessage($user, $entriesByKind);

        if ($cooldownMessage !== null) {
            throw ValidationException::withMessages(['library_file' => $cooldownMessage])
                ->errorBag('importUserLibrary');
        }

        LibraryImporter::dispatch($user, $entriesByKind, ImportService::fromValue((int) $input['import_service']), ImportBehavior::fromValue((int) $input['import_behavior']));
    }
}
