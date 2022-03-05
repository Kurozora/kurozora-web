<x-form-section submit="importLibraryFromMAL">
    <x-slot:title>
        {{ __('Move From MyAnimeList') }}
    </x-slot>

    <x-slot:description>
        {{ __('Export your anime library from MyAnimeList, so you can import it to your Kurozora library.') }}
    </x-slot>

    <x-slot:form>
        <div class="col-span-12">
            <div class="max-w-xl text-sm text-gray-600">
                <p>{{ __('Kurozora does not guarantee all shows will be imported to your library. Once the request has been processed a notification which contains the status of the import request will be sent. Furthermore the uploaded file is deleted as soon as the import request has been processed.') }}</p>

                <br />

                <p>{{ __('Selecting "overwrite" will replace your Kurozora library with the imported one from MyAnimeList.') }}</p>
                <p>{{ __('Selecting "merge" will add missing anime in your Kurozora library. If an anime exists already then the tracking information in your Kurozora library will be updated.') }}</p>
            </div>

            <div class="mt-5">
                <x-select id="import_behavior" wire:model.defer="state.import_behavior">
                    <option value="-1">{{ __('Select behavior') }}</option>
                    @foreach (App\Enums\MALImportBehavior::asSelectArray() as $value => $malImportBehavior)
                        <option value="{{ $value }}">{{ $malImportBehavior }}</option>
                    @endforeach
                </x-select>

                <x-input-error for="import_behavior" class="mt-2" />
            </div>

            <div class="mt-5">
                <x-input-file id="mal_file" accept=".xml" wire:model.defer="state.mal_file" placeholder="Import MAL.xml here" />

                <x-input-error for="mal_file" class="mt-2" />
            </div>
        </div>
    </x-slot>

    <x-slot:actions>
        <x-action-message class="mr-3" on="saved">
            {{ __('Import started.') }}
        </x-action-message>

        <x-button>
            {{ __('Import') }}
        </x-button>
    </x-slot>
</x-form-section>
