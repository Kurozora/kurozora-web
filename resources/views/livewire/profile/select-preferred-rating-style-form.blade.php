<x-form-section submit="updatePreferredRatingStyle">
    <x-slot:title>
        {{ __('Rating Style') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Choose how you want to rate titles.') }}
    </x-slot:description>

    <x-slot:form>
        <div class="col-span-6">
            <div class="max-w-xl text-sm text-primary">
                <p>{{ __('Select your preferred rating style. This will change how you rate anime, manga, and games.') }}</p>
            </div>

            <div class="mt-5">
                <x-select id="rating_style" wire:model="state.rating_style">
                    @foreach ($this->ratingStyles as $style)
                        <option value="{{ $style['value'] }}" {{ $style['value'] == $state['rating_style'] ? 'selected' : '' }}>
                            {{ $style['name'] }} - {{ $style['description'] }}
                        </option>
                    @endforeach
                </x-select>
                <x-input-error for="rating_style" class="mt-2" />
            </div>

            <div class="mt-4 p-4 bg-secondary rounded-lg">
                @switch($state['rating_style'])
                    @case(\App\Enums\RatingStyle::QuickReaction)
                        <div class="flex items-center gap-4">
                            <span class="text-2xl">{{ \App\Enums\RatingStyle::getQuickReactionEmoji(-1) }}</span>
                            <span class="text-2xl">{{ \App\Enums\RatingStyle::getQuickReactionEmoji(0) }}</span>
                            <span class="text-2xl">{{ \App\Enums\RatingStyle::getQuickReactionEmoji(1) }}</span>
                        </div>
                        <p class="mt-2 text-sm text-secondary">{{ __('Quick and simple. Just tap an emoji to rate!') }}</p>
                        @break
                    @case(\App\Enums\RatingStyle::Standard)
                        <div class="flex items-center gap-1">
                            @for ($i = 1; $i <= 5; $i++)
                                @svg('star_fill', 'fill-current', ['width' => '24'])
                            @endfor
                        </div>
                        <p class="mt-2 text-sm text-secondary">{{ __('The classic 5-star rating you know and love.') }}</p>
                        @break
                    @case(\App\Enums\RatingStyle::Advanced)
                        <div class="flex items-center gap-1">
                            @for ($i = 1; $i <= 10; $i++)
                                @svg('star_fill', 'fill-current', ['width' => '16'])
                            @endfor
                        </div>
                        <p class="mt-2 text-sm text-secondary">{{ __('More precise ratings with a 10-star scale.') }}</p>
                        @break
                    @case(\App\Enums\RatingStyle::Detailed)
                        <div class="space-y-1 text-sm">
                            <div class="flex justify-between"><span>{{ __('Story') }}</span><span class="text-secondary">{{ __('/10') }}</span></div>
                            <div class="flex justify-between"><span>{{ __('Characters') }}</span><span class="text-secondary">{{ __('/10') }}</span></div>
                            <div class="flex justify-between"><span>{{ __('Animation') }}</span><span class="text-secondary">{{ __('/10') }}</span></div>
                            <div class="flex justify-between"><span>{{ __('...and more') }}</span></div>
                        </div>
                        <p class="mt-2 text-sm text-secondary">{{ __('Rate multiple aspects for a detailed review.') }}</p>
                        @break
                @endswitch
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
