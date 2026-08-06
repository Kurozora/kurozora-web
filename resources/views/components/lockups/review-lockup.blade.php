@props(['review', 'isRow' => true, 'voteOverrides' => []])

@php
    $class = $isRow ? 'pb-2 shrink-0 snap-normal snap-center' : '';

    $voteOverride = $voteOverrides[$review->id] ?? null;

    if ($voteOverride !== null) {
        $isHelpful = $voteOverride['helpful'] === true;
        $isUnhelpful = $voteOverride['helpful'] === false;
        $helpfulCount = (int) $voteOverride['helpfulCount'];
        $unhelpfulCount = (int) $voteOverride['unhelpfulCount'];
    } else {
        $currentReaction = auth()->user()?->getHelpfulnessFor($review);
        $isHelpful = $currentReaction?->is(\App\Enums\ParentalGuideReaction::Helpful) ?? false;
        $isUnhelpful = $currentReaction?->is(\App\Enums\ParentalGuideReaction::Unhelpful) ?? false;
        $helpfulCount = (int) $review->helpful_count;
        $unhelpfulCount = (int) $review->unhelpful_count;
    }
@endphp

<div {{ $attributes->merge(['class' => 'relative flex-grow w-64 sm:w-96 ' . $class]) }}>
    <div
        class="relative flex flex-row gap-2 pr-2 pl-2 pt-2 pb-2 h-full bg-secondary rounded-xl"
        x-data="{}"
        wire:key="{{ uniqid($review->id, true) }}"
    >
        <x-profile-image-view class="w-12 h-12" :user="$review->user" />

        <div class="flex flex-col items-baseline w-full">
            <div class="flex flex-wrap justify-between w-full">
                <div class="flex flex-wrap gap-1">
                    <a class="inline-flex items-center text-sm font-semibold break-all overflow-hidden" href="{{ route('profile.details', $review->user) }}">{{ $review->user->username }}</a>

                    <livewire:components.user.badge-shelf :user="$review->user" wire:key="{{ uniqid('badges-', true) }}" />
                </div>

                <p class="text-sm text-secondary whitespace-nowrap" title="{{ $review->created_at->toFormattedDateString() }}">{{ $review->created_at->toFormattedDateString() }}</p>
            </div>

            <div>
                <livewire:components.star-rating :rating="$review->rating" :star-size="'sm'" :disabled="true" wire:key="{{ uniqid('rating-', true) }}" />
            </div>

            <div class="mt-2 w-full">
                <x-truncated-text>
                    <x-slot:text>
                        {!! nl2br(e($review->description)) !!}
                    </x-slot:text>
                </x-truncated-text>
            </div>

            <div class="flex gap-2 items-center mt-2">
                <button
                    type="button"
                    class="inline-flex items-center gap-1 pl-2 pr-2 pt-1 pb-1 text-xs rounded-md bg-tertiary {{ $isHelpful ? 'text-tint font-semibold' : '' }}"
                    title="{{ __('Helpful') }}"
                    wire:click="voteOnReview({{ $review->id }}, 'helpful')"
                >
                    <span aria-hidden="true">👍</span>
                    <span>{{ $helpfulCount }}</span>
                </button>

                <button
                    type="button"
                    class="inline-flex items-center gap-1 pl-2 pr-2 pt-1 pb-1 text-xs rounded-md bg-tertiary {{ $isUnhelpful ? 'text-tint font-semibold' : '' }}"
                    title="{{ __('Unhelpful') }}"
                    wire:click="voteOnReview({{ $review->id }}, 'unhelpful')"
                >
                    <span aria-hidden="true">👎</span>
                    <span>{{ $unhelpfulCount }}</span>
                </button>
            </div>

{{--            <div class="flex gap-2 justify-between w-full">--}}
{{--                <div class="flex justify-between">--}}
{{--                    <x-square-button :class="true ? 'text-red-500' : ''">--}}
{{--                        @svg('heart_fill', 'fill-current', ['width' => '18'])--}}
{{--                        <p class="ml-2 mr-2">{{ '1.9K' }}</p>--}}
{{--                    </x-square-button>--}}
{{--                </div>--}}

{{--                <div>--}}
{{--                    <x-dropdown align="right" width="48">--}}
{{--                        <x-slot:trigger>--}}
{{--                            <x-square-button--}}
{{--                                title="{{ __('More') }}"--}}
{{--                            >--}}
{{--                                @svg('ellipsis', 'fill-current', ['width' => '18'])--}}
{{--                            </x-square-button>--}}
{{--                        </x-slot:trigger>--}}

{{--                        <x-slot:content>--}}
{{--                            <button--}}
{{--                                x-data="{--}}
{{--                                    copyTextToClipboard() {--}}
{{--                                        let url = window.location.href + '?id={{ $review->id }}'--}}

{{--                                        navigator.clipboard.writeText(url).then(function() {--}}
{{--                                        }, function(err) {--}}
{{--                                            console.error('Async: Could not copy text: ', err)--}}
{{--                                        })--}}

{{--                                        open = !open--}}
{{--                                    }--}}
{{--                                }"--}}
{{--                                class="block w-full pl-4 pr-4 pt-2 pb-2 text-xs text-center font-semibold hover:bg-tertiary focus:bg-secondary"--}}
{{--                                x-on:click="copyTextToClipboard()"--}}
{{--                            >--}}
{{--                                {{ __('Copy Link') }}--}}
{{--                            </button>--}}
{{--                        </x-slot:content>--}}
{{--                    </x-dropdown>--}}
{{--                </div>--}}
{{--            </div>--}}
        </div>
    </div>
</div>
