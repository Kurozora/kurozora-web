<x-form-section submit="updatePreferredTimezone" wire:init="loadSection">
    <x-slot:title>
        {{ __('Update Timezone') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Select your timezone for accurate schedules.') }}
    </x-slot:description>

    <x-slot:form>
        <div class="col-span-6">
            <div class="max-w-xl text-sm text-primary">
                <p>{{ __('The selected timezone will be used to display all dates and times, including premiere dates, broadcasts, and schedules.') }}</p>
            </div>

            <div class="mt-5">
                <x-select id="timezone" wire:model="state.timezone">
                    @foreach ($this->timezones as $key => $timezone)
                        <option value="{{ $key }}" {{ $key == $state['timezone'] ? 'selected' : '' }}>{{ $timezone }}</option>
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
