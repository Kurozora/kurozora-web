<div>
    <x-dialog-modal :max-width="$isDetailed ? 'lg' : 'md'" :sticky-header="true" model="showPopup">
        <x-slot:title>
            <span class="flex justify-between items-center gap-2">
                <span>{{ __('Write a Review') }}</span>

                @if ($isDetailed)
                    <span
                        class="flex items-center gap-0.5 text-tint"
                        x-data="{ average: null }"
                        x-on:detailed-average-changed.window="average = $event.detail.average"
                        x-show="average !== null"
                        x-cloak
                    >
                        @for ($i = 1; $i <= 5; $i++)
                            <span x-show="average / 2 >= {{ $i }}">@svg('star_fill', 'fill-current', ['width' => '16'])</span>
                            <span x-show="average / 2 >= {{ $i - 0.5 }} && average / 2 < {{ $i }}">@svg('star_leadinghalf_fill', 'fill-current', ['width' => '16'])</span>
                            <span x-show="average / 2 < {{ $i - 0.5 }}">@svg('star', 'fill-current', ['width' => '16'])</span>
                        @endfor
                    </span>
                @endif
            </span>
        </x-slot:title>

        <x-slot:content>
            <div class="flex flex-col gap-4 pt-4 pb-4 pl-4 pr-4">
                @if ($isDetailed)
                    <div
                        class="flex flex-col gap-6"
                        wire:key="review-box-detailed-{{ md5(json_encode($scores)) }}"
                        x-data="{
                            scores: @js($scores),
                            weights: @js($this->categories->mapWithKeys(fn ($ratingCategory) => [(string) $ratingCategory->id => $ratingCategory->weight])->toArray()),
                            get average() {
                                let weightedSum = 0
                                let totalWeight = 0
                                Object.keys(this.weights).forEach((key) => {
                                    weightedSum += (parseFloat(this.scores[key]) || 0) * this.weights[key]
                                    totalWeight += this.weights[key]
                                })
                                return totalWeight > 0 ? Math.round(weightedSum / totalWeight * 10) / 10 : 0
                            }
                        }"
                        x-effect="$dispatch('detailed-average-changed', { average: average })"
                    >
                        @foreach ($this->categories as $ratingCategory)
                            @php
                                $categoryKey = (string) $ratingCategory->id;
                            @endphp

                            <div class="flex flex-col gap-2">
                                <div class="flex justify-between items-baseline">
                                    <p class="text-secondary text-sm font-semibold">{{ $ratingCategory->name }}</p>

                                    <p class="text-secondary" x-text="'{{ __(':x / 10.0') }}'.replace(':x', (parseFloat(scores['{{ $categoryKey }}']) || 0).toFixed(1))"></p>
                                </div>

                                @if (!empty($ratingCategory->description))
                                    <p class="text-secondary text-sm">{{ $ratingCategory->description }}</p>
                                @endif

                                <input
                                    class="w-full cursor-pointer"
                                    style="accent-color: var(--tint-color);"
                                    type="range"
                                    min="{{ \App\Models\RatingCategoryScore::MIN_SCORE_VALUE }}"
                                    max="{{ \App\Models\RatingCategoryScore::MAX_SCORE_VALUE }}"
                                    step="0.5"
                                    wire:model="scores.{{ $categoryKey }}"
                                    x-on:input="scores['{{ $categoryKey }}'] = $event.target.value"
                                />

                                <x-textarea class="block w-full h-48 mt-1 resize-none" placeholder="{{ __('What’s on your mind?') }}" wire:model="categoryReviews.{{ $categoryKey }}"></x-textarea>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex items-center">
                        <p class="">{{ __('Click to Rate:') }}</p>

                        <livewire:components.rating-input :model-id="$modelID" :model-type="$modelType" :rating="$rating" :star-size="'md'" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <p class="text-secondary text-sm font-semibold">{{ __('Review') }}</p>

                        <x-textarea class="block w-full h-48 mt-1 resize-none" placeholder="{{ __('What’s on your mind?') }}" wire:model="reviewText"></x-textarea>
                    </div>
                @endif

                <x-hr />

                <div class="flex flex-col gap-2">
                    <p class="text-secondary text-sm font-semibold">{{ __('Private Notes') }}</p>

                    <x-textarea class="block w-full h-48 mt-1 resize-none" placeholder="{{ __('What’s on your mind?') }}" wire:model="noteText"></x-textarea>
                </div>
            </div>
        </x-slot:content>

        <x-slot:footer>
            <div class="flex justify-between items-center">
                <div>
                    @if ($isDetailed && $rating !== null)
                        <x-danger-button wire:click="$toggle('confirmingRemoval')" wire:loading.attr="disabled">
                            {{ __('Remove') }}
                        </x-danger-button>
                    @endif
                </div>

                <div class="flex items-center">
                    <x-outlined-button wire:click="$toggle('showPopup')" wire:loading.attr="disabled">
                        {{ __('Cancel') }}
                    </x-outlined-button>

                    <x-button class="ml-2" wire:click="submitReview" wire:loading.attr="disabled">
                        {{ __('Submit') }}
                    </x-button>
                </div>
            </div>
        </x-slot:footer>
    </x-dialog-modal>

    <x-dialog-modal model="confirmingRemoval">
        <x-slot:title>
            {{ __('Remove Rating') }}
        </x-slot:title>

        <x-slot:content>
            <div class="pt-4 pb-4 pl-4 pr-4">
                <p>{{ __('Removing your rating will also delete your review. Do you want to continue?') }}</p>
            </div>
        </x-slot:content>

        <x-slot:footer>
            <x-outlined-button wire:click="$toggle('confirmingRemoval')" wire:loading.attr="disabled">
                {{ __('Nevermind') }}
            </x-outlined-button>

            <x-danger-button class="ml-2" wire:click="removeRating" wire:loading.attr="disabled">
                {{ __('Remove') }}
            </x-danger-button>
        </x-slot:footer>
    </x-dialog-modal>
</div>
