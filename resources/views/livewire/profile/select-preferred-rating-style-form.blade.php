<x-form-section submit="updatePreferredRatingStyle">
    <x-slot:title>
        {{ __('Update Rating Style') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Select how you want to rate titles.') }}
    </x-slot:description>

    <x-slot:form>
        <div class="col-span-6">
            <div class="max-w-xl text-sm text-primary">
                <p>{{ __('Rating styles range from a quick emoji reaction to a detailed per-category review. Your choice changes how you rate anime, manga, and games.') }}</p>
            </div>

            <div class="mt-5">
                <x-select id="rating_style" wire:model.live="state.rating_style">
                    @foreach ($this->ratingStyles as $value => $description)
                        <option value="{{ $value }}" {{ $value == $state['rating_style'] ? 'selected' : '' }}>{{ $description }}</option>
                    @endforeach
                </x-select>
                <x-input-error for="rating_style" class="mt-2" />
            </div>

            <div class="flex flex-col gap-2 mt-4 pt-4 pr-4 pb-4 pl-4 bg-secondary rounded-lg">
                @switch((int) $state['rating_style'])
                    @case(\App\Enums\RatingStyle::QuickReaction)
                        <div
                            class="flex flex-col gap-2"
                            x-data="{
                                selected: null
                            }"
                        >
                            <div class="flex items-center gap-4">
                                @foreach (\App\Enums\EmojiScore::getInstances() as $instance)
                                    <button
                                        type="button"
                                        class="transition ease-in-out duration-150"
                                        x-bind:class="{ 'opacity-50': selected !== null && selected !== {{ $instance->value }} }"
                                        x-on:click="selected !== {{ $instance->value }} ? selected = {{ $instance->value }} : selected = null"
                                    >
                                        <span
                                            class="flex items-center justify-center w-10 h-10 text-2xl leading-none rounded-full"
                                            x-bind:class="{ 'bg-tinted': selected === {{ $instance->value }} }"
                                        >{{ $instance->emoji() }}</span>
                                    </button>
                                @endforeach
                            </div>

                            <p class="text-sm text-tint" x-show="selected === {{ \App\Enums\EmojiScore::Disliked }}" x-cloak>{{ __("The exact feeling of watching a legendary manga get ruined by a bad studio.") }}</p>
                            <p class="text-sm text-tint" x-show="selected === {{ \App\Enums\EmojiScore::Neutral }}" x-cloak>{{ __('The only good thing about the entire show was the opening song.') }}</p>
                            <p class="text-sm text-tint" x-show="selected === {{ \App\Enums\EmojiScore::Liked }}" x-cloak>{{ __('The rare 10/10 that actually lives up to the massive internet hype.') }}</p>
                        </div>

                        <p class="text-sm text-secondary">{{ __('Quick and simple. Just tap an emoji to rate.') }}</p>

                        <div class="flex flex-col gap-1 mt-2">
                            <p class="text-sm text-secondary">{{ __('Each reaction is automatically saved as a star rating:') }}</p>

                            @foreach (\App\Enums\EmojiScore::getInstances() as $instance)
                                <div class="flex items-center gap-2">
                                    <p class="flex justify-center w-8 text-lg leading-none">{{ $instance->emoji() }}</p>

                                    <div class="flex items-center gap-0.5 text-tint">
                                        @for ($i = 1; $i <= 5; $i++)
                                            @if ($instance->score() >= $i)
                                                @svg('star_fill', 'fill-current', ['width' => '14'])
                                            @elseif ($instance->score() >= $i - 0.5)
                                                @svg('star_leadinghalf_fill', 'fill-current', ['width' => '14'])
                                            @else
                                                @svg('star', 'fill-current', ['width' => '14'])
                                            @endif
                                        @endfor
                                    </div>

                                    <p class="text-sm text-secondary">{{ __(':x out of 5 stars', ['x' => $instance->score() + 0]) }}</p>
                                </div>
                            @endforeach
                        </div>
                        @break
                    @case(\App\Enums\RatingStyle::Detailed)
                        <div class="flex flex-col gap-1 text-sm">
                            <div class="flex justify-between"><p>{{ __('Story') }}</p><p class="text-secondary">{{ __('8.5 / 10.0') }}</p></div>
                            <div class="flex justify-between"><p>{{ __('Characters') }}</p><p class="text-secondary">{{ __('7.0 / 10.0') }}</p></div>
                            <div class="flex justify-between"><p>{{ __('…and more') }}</p></div>
                        </div>

                        <p class="text-sm text-secondary">{{ __('Score every aspect, such as story and characters, for a detailed review.') }}</p>
                        @break
                    @default
                        @php($initialValue = 3.5)
                        <div class="flex items-center gap-1 text-tint" x-data="{ value: {{ $initialValue }} }">
                            @for ($i = 1; $i <= 5; $i++)
                                <div class="relative">
                                    <button type="button" class="absolute top-0 bottom-0 left-0 w-1/2" style="z-index: 1;" x-on:click="value = {{ $i - 0.5 }}"></button>
                                    <button type="button" class="absolute top-0 bottom-0 right-0 w-1/2" x-on:click="value = {{ $i }}"></button>

                                    <span x-show="value >= {{ $i }}" @if ($initialValue < $i) x-cloak @endif>@svg('star_fill', 'fill-current', ['width' => '24'])</span>
                                    <span x-show="value === {{ $i - 0.5 }}" @if ($initialValue != $i - 0.5) x-cloak @endif>@svg('star_leadinghalf_fill', 'fill-current', ['width' => '24'])</span>
                                    <span x-show="value < {{ $i - 0.5 }}" @if ($initialValue >= $i - 0.5) x-cloak @endif>@svg('star', 'fill-current', ['width' => '24'])</span>
                                </div>
                            @endfor
                        </div>

                        <p class="text-sm text-secondary">{{ __('The classic 5-star rating you know and love.') }}</p>
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
