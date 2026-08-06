@php
    $authUser = auth()->user();
    $voteOverride = $voteOverrides[$entry->id] ?? null;

    if ($voteOverride !== null) {
        $isHelpful = $voteOverride['helpful'] === true;
        $isUnhelpful = $voteOverride['helpful'] === false;
        $helpfulCount = (int) $voteOverride['helpfulCount'];
        $unhelpfulCount = (int) $voteOverride['unhelpfulCount'];
    } else {
        $currentReaction = $authUser?->getHelpfulnessFor($entry);
        $isHelpful = $currentReaction?->is(\App\Enums\ParentalGuideReaction::Helpful) ?? false;
        $isUnhelpful = $currentReaction?->is(\App\Enums\ParentalGuideReaction::Unhelpful) ?? false;
        $helpfulCount = (int) $entry->helpful_count;
        $unhelpfulCount = (int) $entry->unhelpful_count;
    }

    $descriptorParts = array_filter([
        $entry->frequency?->description,
        $entry->category->supportsDepiction() ? $entry->depiction?->description : null,
        $entry->rating->description,
    ]);
    $descriptor = implode(' · ', $descriptorParts);
@endphp

<div
    id="entry-{{ $entry->id }}"
    class="relative w-full max-w-prose bg-secondary rounded-md"
    wire:key="parental-guide-entry-{{ $entry->id }}"
    x-data="{
        isDisabled: false,
        init() {
            const dismissed = sessionStorage.getItem('pg-spoiler-dismissed-' + @js($entry->id))
            this.isDisabled = @js($entry->is_spoiler) && !dismissed
        },
        dismissSpoiler() {
            this.isDisabled = false
            sessionStorage.setItem('pg-spoiler-dismissed-' + @js($entry->id), '1')
        },
    }"
>
    <div
        class="flex flex-col gap-2 pt-4 pb-4 pl-4 pr-4"
        x-bind:class="{'invisible' : isDisabled}"
    >
        @if ($descriptor !== '')
            <p class="text-secondary text-xs">{{ $descriptor }}</p>
        @endif

        <p style="white-space: pre-wrap; overflow-wrap: break-word;">{{ $entry->reason }}</p>

        <div class="flex justify-between items-center">
            <div class="flex gap-2 items-center">
                <button
                    type="button"
                    class="inline-flex items-center gap-1 pl-2 pr-2 pt-1 pb-1 text-xs rounded-md bg-tertiary {{ $isHelpful ? 'text-tint font-semibold' : '' }}"
                    title="{{ __('Helpful') }}"
                    wire:click="vote({{ $entry->id }}, 'helpful')"
                >
                    <span aria-hidden="true">👍</span>
                    <span>{{ $helpfulCount }}</span>
                </button>

                <button
                    type="button"
                    class="inline-flex items-center gap-1 pl-2 pr-2 pt-1 pb-1 text-xs rounded-md bg-tertiary {{ $isUnhelpful ? 'text-tint font-semibold' : '' }}"
                    title="{{ __('Unhelpful') }}"
                    wire:click="vote({{ $entry->id }}, 'unhelpful')"
                >
                    <span aria-hidden="true">👎</span>
                    <span>{{ $unhelpfulCount }}</span>
                </button>
            </div>

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
                            class="block w-full pl-4 pr-4 pt-2 pb-2 text-primary text-xs text-center font-semibold hover:bg-tertiary focus:bg-secondary {{ $isHelpful ? 'text-tint' : '' }}"
                            wire:click="vote({{ $entry->id }}, 'helpful')"
                        >
                            {{ __('Helpful') }}
                        </button>

                        <button
                            type="button"
                            class="block w-full pl-4 pr-4 pt-2 pb-2 text-primary text-xs text-center font-semibold hover:bg-tertiary focus:bg-secondary {{ $isUnhelpful ? 'text-tint' : '' }}"
                            wire:click="vote({{ $entry->id }}, 'unhelpful')"
                        >
                            {{ __('Unhelpful') }}
                        </button>

                        @auth
                            <x-hr />

                            @if (auth()->id() === $entry->user_id)
                                <button
                                    type="button"
                                    class="block w-full pl-4 pr-4 pt-2 pb-2 text-primary text-xs text-center font-semibold hover:bg-tertiary focus:bg-secondary"
                                    wire:click="openEditForm({{ $entry->id }})"
                                >
                                    {{ __('Edit') }}
                                </button>

                                <button
                                    type="button"
                                    class="block w-full pl-4 pr-4 pt-2 pb-2 text-red-500 text-xs text-center font-semibold hover:bg-tertiary focus:bg-secondary"
                                    wire:click="deleteEntry({{ $entry->id }})"
                                    wire:confirm="{{ __('Delete this entry?') }}"
                                >
                                    {{ __('Delete') }}
                                </button>
                            @else
                                <button
                                    type="button"
                                    class="block w-full pl-4 pr-4 pt-2 pb-2 text-red-500 text-xs text-center font-semibold hover:bg-tertiary focus:bg-secondary"
                                    wire:click="openReportForm({{ $entry->id }})"
                                >
                                    {{ __('Report') }}
                                </button>
                            @endif
                        @endauth
                    </x-slot:content>
                </x-dropdown>
            </div>
        </div>
    </div>

    <button
        type="button"
        class="absolute inset-0 backdrop-blur bg-tertiary text-sm rounded-md text-center"
        x-show="isDisabled"
        x-on:click="dismissSpoiler()"
        x-cloak
    >
        <p>{{ __('This reason contains spoilers — click to view') }}</p>
    </button>
</div>
