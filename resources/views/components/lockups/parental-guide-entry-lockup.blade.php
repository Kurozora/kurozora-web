@props(['entry'])

@php
    $currentReaction = auth()->user()?->getHelpfulnessFor($entry);
    $helpful = match (true) {
        $currentReaction === null => null,
        $currentReaction->is(\App\Enums\ParentalGuideReaction::Helpful) => true,
        default => false,
    };

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
    key="parental-guide-entry-{{ $entry->id }}"
    x-data="parentalGuideEntryLockup({{ Js::from([
        'id' => $entry->id,
        'spoiler' => (bool) $entry->is_spoiler,
        'helpful' => $helpful,
        'helpfulCount' => (int) $entry->helpful_count,
        'unhelpfulCount' => (int) $entry->unhelpful_count,
        'authenticated' => auth()->check(),
        'signInUrl' => route('sign-in'),
    ]) }})"
    x-on:parental-guide-voted.window="syncVote($event.detail)"
    x-on:parental-guide-deleted.window="syncDelete($event.detail)"
    x-on:user-actions-failed.window="busy = false"
    x-show="!deleted"
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
                    class="inline-flex items-center gap-1 pl-2 pr-2 pt-1 pb-1 text-xs rounded-md bg-tertiary"
                    title="{{ __('Helpful') }}"
                    x-on:click="vote('helpful')"
                    x-bind:class="{ 'text-tint font-semibold': helpful === true }"
                >
                    <span aria-hidden="true">👍</span>
                    <span x-text="helpfulCount"></span>
                </button>

                <button
                    type="button"
                    class="inline-flex items-center gap-1 pl-2 pr-2 pt-1 pb-1 text-xs rounded-md bg-tertiary"
                    title="{{ __('Unhelpful') }}"
                    x-on:click="vote('unhelpful')"
                    x-bind:class="{ 'text-tint font-semibold': helpful === false }"
                >
                    <span aria-hidden="true">👎</span>
                    <span x-text="unhelpfulCount"></span>
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
                            class="block w-full pl-4 pr-4 pt-2 pb-2 text-primary text-xs text-center font-semibold hover:bg-tertiary focus:bg-secondary"
                            x-on:click="vote('helpful')"
                            x-bind:class="{ 'text-tint': helpful === true }"
                        >
                            {{ __('Helpful') }}
                        </button>

                        <button
                            type="button"
                            class="block w-full pl-4 pr-4 pt-2 pb-2 text-primary text-xs text-center font-semibold hover:bg-tertiary focus:bg-secondary"
                            x-on:click="vote('unhelpful')"
                            x-bind:class="{ 'text-tint': helpful === false }"
                        >
                            {{ __('Unhelpful') }}
                        </button>

                        @auth
                            <x-hr />

                            @if (auth()->id() === $entry->user_id)
                                <button
                                    type="button"
                                    class="block w-full pl-4 pr-4 pt-2 pb-2 text-primary text-xs text-center font-semibold hover:bg-tertiary focus:bg-secondary"
                                    x-on:click="edit()"
                                >
                                    {{ __('Edit') }}
                                </button>

                                <button
                                    type="button"
                                    class="block w-full pl-4 pr-4 pt-2 pb-2 text-red-500 text-xs text-center font-semibold hover:bg-tertiary focus:bg-secondary"
                                    x-on:click="remove()"
                                >
                                    {{ __('Delete') }}
                                </button>
                            @else
                                <button
                                    type="button"
                                    class="block w-full pl-4 pr-4 pt-2 pb-2 text-red-500 text-xs text-center font-semibold hover:bg-tertiary focus:bg-secondary"
                                    x-on:click="report()"
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
