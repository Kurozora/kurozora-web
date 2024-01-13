<x-form-section submit="updatePreferredTimezone" wire:init="loadSection">
    <x-slot:title>
        {{ __('Update Timezone') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Select the shows that are allowed to be shown.') }}
    </x-slot:description>

    <x-slot:form>
        <div class="col-span-12">
            <div class="max-w-xl text-sm text-gray-600">
                <p>{{ __('TV ratings are tiered. Depending on the chosen TV rating some shows might be hidden.') }}</p>
                <br/>
                <p>{{ __('For example, selecting R15+ will show you all anime up to a TV rating of R15+.') }}</p>
            </div>

            <div class="mt-5">
                <x-select id="timezone" wire:model.defer="state.timezone">
                    @foreach ($this->timezones as $key => $timezone)
                        <option value="{{ $key }}">{{ $timezone }}</option>
                    @endforeach
                </x-select>
                <x-input-error for="timezone" class="mt-2" />
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
