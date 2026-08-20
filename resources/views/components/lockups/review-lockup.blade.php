@props(['review', 'isRow' => true, 'voteOverrides' => []])

@php
    $class = $isRow ? 'pb-2 shrink-0 snap-normal snap-center' : '';

    $progressTotal = $review->relationLoaded('model') && $review->model instanceof \App\Models\Anime
        ? $review->model->episode_count
        : null;

    $revisions = $review->relationLoaded('revisions') ? $review->revisions : collect();

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
        x-data="{
            isDisabled: false,
            init() {
                const dismissed = sessionStorage.getItem('review-spoiler-dismissed-' + @js($review->id))
                this.isDisabled = @js($review->is_spoiler) && !dismissed
            },
            dismissSpoiler() {
                this.isDisabled = false
                sessionStorage.setItem('review-spoiler-dismissed-' + @js($review->id), '1')
            },
        }"
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

            <div class="flex items-center gap-2">
                <livewire:components.star-rating :rating="$review->rating" :star-size="'sm'" :disabled="true" wire:key="{{ uniqid('rating-', true) }}" />

                @if ($review->is_elevated)
                    <span class="pl-2 pr-2 pt-1 pb-1 text-xs rounded-md bg-tertiary text-tint font-semibold">{{ __('Community Pick') }}</span>
                @endif

                @if ($review->recommendation !== null)
                    <span class="pl-2 pr-2 pt-1 pb-1 text-xs rounded-md bg-tertiary">{{ $review->recommendation->description }}</span>
                @endif

                @if ($review->progress !== null)
                    <span class="pl-2 pr-2 pt-1 pb-1 text-xs rounded-md bg-tertiary">{{ $progressTotal !== null ? __('Ep :x/:y', ['x' => $review->progress, 'y' => $progressTotal]) : __('Ep :x', ['x' => $review->progress]) }}</span>
                @endif
            </div>

            <div class="relative mt-2 w-full">
                <div x-bind:class="{'invisible' : isDisabled}">
                    <x-truncated-text>
                        <x-slot:text>
                            {!! nl2br(e($review->description)) !!}
                        </x-slot:text>
                    </x-truncated-text>
                </div>

                <button
                    type="button"
                    class="absolute inset-0 backdrop-blur bg-tertiary text-sm rounded-md text-center"
                    x-show="isDisabled"
                    x-on:click="dismissSpoiler()"
                    x-cloak
                >
                    <p>{{ __('This review contains spoilers. Click to view') }}</p>
                </button>
            </div>

            @if ($revisions->isNotEmpty())
                <div class="w-full mt-2" x-data="{ isExpanded: false }">
                    <button
                        type="button"
                        class="flex items-center gap-1 pl-2 pr-2 pt-1 pb-1 text-xs text-secondary rounded-md bg-tertiary"
                        x-on:click="isExpanded = !isExpanded"
                    >
                        <span x-show="!isExpanded">{{ __('Show earlier versions (:count)', ['count' => $revisions->count()]) }}</span>
                        <span x-show="isExpanded" x-cloak>{{ __('Hide earlier versions') }}</span>
                    </button>

                    <div class="flex flex-col gap-3 mt-2" x-show="isExpanded" x-cloak>
                        @foreach ($revisions as $revision)
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center gap-1 pl-2 pr-2 pt-1 pb-1 text-xs rounded-md bg-tertiary">
                                        @svg('star_fill', 'fill-current', ['width' => 10])
                                        {{ number_format($revision->rating, 1) }}
                                    </span>

                                    @if ($revision->recommendation !== null)
                                        <span class="pl-2 pr-2 pt-1 pb-1 text-xs rounded-md bg-tertiary">{{ $revision->recommendation->description }}</span>
                                    @endif

                                    @if ($revision->progress !== null)
                                        <span class="pl-2 pr-2 pt-1 pb-1 text-xs rounded-md bg-tertiary">{{ $progressTotal !== null ? __('Ep :x/:y', ['x' => $revision->progress, 'y' => $progressTotal]) : __('Ep :x', ['x' => $revision->progress]) }}</span>
                                    @endif

                                    <p class="text-xs text-secondary whitespace-nowrap">{{ $revision->written_at->toFormattedDateString() }}</p>
                                </div>

                                <div class="relative mt-1" x-data="{ isDisabled: @js($revision->is_spoiler) }">
                                    <p class="text-sm" x-bind:class="{'invisible' : isDisabled}">{!! nl2br(e($revision->description)) !!}</p>

                                    <button
                                        type="button"
                                        class="absolute inset-0 backdrop-blur bg-tertiary text-sm rounded-md text-center"
                                        x-show="isDisabled"
                                        x-on:click="isDisabled = false"
                                        x-cloak
                                    >
                                        <p>{{ __('This review contains spoilers. Click to view') }}</p>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (auth()->id() !== $review->user_id)
                <div class="flex justify-between items-center w-full mt-2">
                    <div class="flex gap-2 items-center">
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

                    @auth
                        <div class="relative">
                            <x-dropdown align="right" width="48">
                                <x-slot:trigger>
                                    <x-square-button title="{{ __('More') }}">
                                        @svg('ellipsis', 'fill-current', ['width' => '18'])
                                    </x-square-button>
                                </x-slot:trigger>

                                <x-slot:content>
                                    <button
                                        type="button"
                                        class="block w-full pl-4 pr-4 pt-2 pb-2 text-red-500 text-xs text-center font-semibold hover:bg-tertiary focus:bg-secondary"
                                        wire:click="openReviewReportForm({{ $review->id }})"
                                    >
                                        {{ __('Report') }}
                                    </button>
                                </x-slot:content>
                            </x-dropdown>
                        </div>
                    @endauth
                </div>
            @endif

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
