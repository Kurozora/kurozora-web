<x-form-section submit="importUserLibrary">
    <x-slot:title>
        {{ __('Move From Another Service') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Export your library from other services, so you can import it to your :x library.', ['x' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:form>
        <div class="col-span-12">
            <div class="max-w-xl text-sm text-primary">
                <p>{{ __(':x does not guarantee all shows and mangas will be imported to your library. Once the request has been processed a notification which contains the status of the import request will be sent. Furthermore, the uploaded file is deleted as soon as the import request has been processed.', ['x' => config('app.name')]) }}</p>

                <br/>

                <p>{{ __('Selecting "overwrite" will replace your :x library with the imported one from the file.', ['x' => config('app.name')]) }}</p>
                <p>{{ __('Selecting "merge" will add missing items to your :x library. If an item exists then the tracking information in your :x library will be updated with the imported one from the file.', ['x' => config('app.name')]) }}</p>
            </div>

            <div class="mt-5">
                <x-select id="library" wire:model="state.library">
                    <option value="-1">{{ __('Select library') }}</option>
                    @foreach (App\Enums\UserLibraryKind::asSelectArray() as $value => $libraryKind)
                        <option value="{{ $value }}">{{ $libraryKind }}</option>
                    @endforeach
                </x-select>

                <x-input-error for="library" class="mt-2"/>
            </div>

            <div class="mt-5">
                <x-select id="import_service" wire:model="state.import_service">
                    <option value="-1">{{ __('Select service') }}</option>
                    @foreach (App\Enums\ImportService::asSelectArray() as $value => $importService)
                        <option value="{{ $value }}">{{ $importService }}</option>
                    @endforeach
                </x-select>

                <x-input-error for="import_service" class="mt-2"/>
            </div>

            <div class="mt-5">
                <x-select id="import_behavior" wire:model="state.import_behavior">
                    <option value="-1">{{ __('Select behavior') }}</option>
                    @foreach (App\Enums\ImportBehavior::asSelectArray() as $value => $importBehavior)
                        <option value="{{ $value }}">{{ $importBehavior }}</option>
                    @endforeach
                </x-select>

                <x-input-error for="import_behavior" class="mt-2"/>
            </div>

            <div class="mt-5">
                <x-input-file id="library_file" accept=".xml" wire:model="state.library_file"
                              placeholder="Import Anime.xml here"/>

                <x-input-error for="library_file" class="mt-2"/>
            </div>
        </div>
    </x-slot:form>

    <x-slot:actions>
        <x-action-message class="mr-3" on="saved">
            {{ __('Import started.') }}
        </x-action-message>

        <x-button>
            {{ __('Import') }}
        </x-button>
    </x-slot:actions>
</x-form-section>
