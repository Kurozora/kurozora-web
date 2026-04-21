<div class="flex flex-col items-center gap-2">
    @switch($this->userRatingStyle->value)
        @case(\App\Enums\RatingStyle::QuickReaction)
            <livewire:components.quick-reaction-rating
                    :model-id="$modelID"
                    :model-type="$modelType"
                    :rating="$rating"
                    :disabled="$disabled"
                    :key="'quick-reaction-' . $modelID . '-' . $modelType"
            />
            @break

        @case(\App\Enums\RatingStyle::Advanced)
            <livewire:components.advanced-star-rating
                    :model-id="$modelID"
                    :model-type="$modelType"
                    :rating="$rating"
                    :star-size="$starSize"
                    :disabled="$disabled"
                    :key="'advanced-star-' . $modelID . '-' . $modelType"
            />
            @break

        @case(\App\Enums\RatingStyle::Detailed)
            <livewire:components.detailed-rating
                    :model-id="$modelID"
                    :model-type="$modelType"
                    :rating="$rating"
                    :disabled="$disabled"
                    :key="'detailed-' . $modelID . '-' . $modelType"
            />
            @break

        @default
            {{-- Standard 5-star rating (default) --}}
            {{-- Convert internal 0-10 to 0-5 for display --}}
            <livewire:components.star-rating
                    :model-id="$modelID"
                    :model-type="$modelType"
                    :rating="\App\Enums\RatingStyle::internalToStandard($rating ?? 0)"
                    :star-size="$starSize"
                    :disabled="$disabled"
                    :key="'star-' . $modelID . '-' . $modelType"
            />
    @endswitch

    {{-- Elaborate prompt for standard ratings --}}
    @if ($showElaboratePrompt && !$disabled && $this->userRatingStyle->value === \App\Enums\RatingStyle::Standard)
        <div class="flex items-center gap-2 mt-2 px-3 py-2 bg-secondary/50 rounded-lg animate-fade-in">
            <span class="text-xs text-secondary">{{ __('Want to share more details?') }}</span>
            <button
                    type="button"
                    x-data
                    x-on:click="$dispatch('open-detailed-modal', { modelId: '{{ $modelID }}', modelType: '{{ $modelType }}' })"
                    class="text-xs font-medium text-tint hover:underline"
            >
                {{ __('Elaborate') }}
            </button>
            <button
                    type="button"
                    wire:click="dismissElaboratePrompt"
                    class="ml-auto p-1 text-secondary hover:text-primary transition-colors"
            >
                @svg('xmark', 'w-3 h-3 fill-current')
            </button>
        </div>
    @endif
</div>
