<x-form-section submit="updatePreferredLanguage" wire:init="loadSection">
    <x-slot:title>
        {{ __('Update Language') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Select the language you would like used throughout Kurozora.') }}
    </x-slot:description>

    <x-slot:form>
        <div class="col-span-12">
            <div class="max-w-xl text-sm text-gray-600">
                <p>{{ __('Changing the language will update the language of the user interface as well as the language of the information shown throughout the website.') }}</p>
                <p>{{ __('These changes will also take effect in the app, and anywhere you are signed in with your Kurozora ID.') }}</p>
                <br />
                <p>{{ __('If an information cannot be shown in your preferred language, English will be used instead.') }}</p>
            </div>

            <div class="mt-5">
                <x-select id="language" wire:model="state.language">
                    @foreach ($this->languages as $language)
                        <option value="{{ $language->code }}">{{ $language->name }}</option>
                    @endforeach
                </x-select>
                <x-input-error for="language" class="mt-2" />
            </div>
        </div>
    </x-slot:form>

    <x-slot:actions>
        <x-action-message class="mr-3" on="saved">
            {{ __('Saved.') }}
        </x-action-message>

        <x-button>
            {{ __('Save') }}
        </x-button>
    </x-slot:actions>
</x-form-section>
