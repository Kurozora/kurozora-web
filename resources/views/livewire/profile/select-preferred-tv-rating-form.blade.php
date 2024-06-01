<x-form-section submit="updatePreferredTvRating" wire:init="loadSection">
    <x-slot:title>
        {{ __('Update TV Rating') }}
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
                <x-select id="tv_rating" wire:model="state.tv_rating">
                    <option value="-1">{{ __('Allow All Titles') }}</option>
                    @foreach ($this->tvRatings as $tvRating)
                        <option value="{{ $tvRating->weight }}" {{ $tvRating->weight == $state['tv_rating'] ? 'selected' : '' }}>{{ $tvRating->full_name }}</option>
                    @endforeach
                </x-select>
                <x-input-error for="tv_rating" class="mt-2" />
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
