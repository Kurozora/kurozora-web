<div class="relative flex flex-col items-end gap-2">
    @switch($this->userRatingStyle->value)
        @case(\App\Enums\RatingStyle::QuickReaction)
            <livewire:components.quick-reaction-rating
                :model-id="$modelID"
                :model-type="$modelType"
                :rating="$rating"
                :disabled="$disabled"
                :allows-remove="!$disabled"
                :key="'quick-reaction-rating-' . $modelID . '-' . $modelType"
            />
            @break
        @case(\App\Enums\RatingStyle::Detailed)
            <button
                type="button"
                title="{{ __('Write a Review') }}"
                wire:click="$dispatch('show-review-box', { 'id': '{{ $reviewBoxID }}' })"
            >
                <livewire:components.star-rating
                    :rating="$rating"
                    :star-size="$starSize"
                    :disabled="true"
                    :allows-remove="true"
                    :key="'detailed-star-rating-' . $rating . '-' . $modelID . '-' . $modelType"
                />
            </button>
            @break
        @default
            <livewire:components.star-rating
                :model-id="$modelID"
                :model-type="$modelType"
                :rating="$rating"
                :star-size="$starSize"
                :disabled="$disabled"
                :allows-remove="!$disabled"
                :key="'star-rating-' . $modelID . '-' . $modelType"
            />

            @if ($showElaboratePrompt && !$disabled)
                <x-tooltip
                    class="top-full right-0 mt-1"
                    x-data="{ shown: localStorage.getItem('elaborate-prompt-shown') === '1' }"
                    x-init="if (!shown) { localStorage.setItem('elaborate-prompt-shown', '1') }"
                    x-show="!shown"
                >
                    <div class="flex flex-col gap-2">
                        <p class="text-xs font-semibold">{{ __('Got more to say than stars can tell?') }}</p>

                        <p class="text-xs text-secondary">{{ __('Give the detailed review style a try and score every aspect on its own. Not your thing? Switch back anytime in your profile settings.') }}</p>

                        <div class="flex justify-between items-center gap-2">
                            <button class="text-xs font-semibold text-tint hover:underline" wire:click="switchToDetailed">
                                {{ __('Try It Now') }}
                            </button>

                            <button class="text-xs text-secondary hover:text-primary" wire:click="dismissElaboratePrompt">
                                {{ __('No Thanks') }}
                            </button>
                        </div>
                    </div>
                </x-tooltip>
            @endif
    @endswitch
</div>
