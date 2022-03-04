<x-form-section submit="updatePreferredTvRating">
    <x-slot:title>
        {{ __('Update TV Rating') }}
    </x-slot>

    <x-slot:description>
        {{ __('Select the shows that are allowed to be shown.') }}
    </x-slot>

    <x-slot:form>
        <div class="col-span-12">
            <div class="max-w-xl text-sm text-gray-600">
                {{ __('Depending on the chosen TV rating some shows might be hidden.') }}
            </div>

            <div class="mt-5">
                <x-select id="tv_rating" wire:model.defer="state.tv_rating">
                    <option value="-1">{{ __('Allow All Shows') }}</option>
                    @foreach (App\Models\TvRating::all()->where('id', '!=', 1) as $tvRating)
                        <option value="{{ $tvRating->weight }}">{{ $tvRating->full_name }}</option>
                    @endforeach
                </x-select>
                <x-input-error for="tv_rating" class="mt-2" />
            </div>
        </div>
    </x-slot>

    <x-slot:actions>
        <x-action-message class="mr-3" on="saved">
            {{ __('Saved.') }}
        </x-action-message>

        <x-button>
            {{ __('Save') }}
        </x-button>
    </x-slot>
</x-form-section>
