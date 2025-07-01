@php
    $authUser = auth()->user();
    $totalHearts = $feedMessage->viaLoveReactant()->getReactionCounterOfType(\App\Enums\FeedVoteType::Heart()->description);
    $isHearted = $authUser && $authUser->getCurrentHeartValueFor($feedMessage) == \App\Enums\FeedVoteType::Heart;
@endphp

<article
    class="relative flex flex-row gap-2 pr-4 pl-4 pt-4 pb-2 border-b border-primary"
    x-data="{
        displayContent: '',

        init() {
            const content = @js($feedMessage->content)

            this.displayContent = markdown.parse(content.replace(/(?:https?|http):\/\/[\n\S]+$/, '').trim(), 0, null, true)
        }
    }"
    x-init="init()"
    wire:key="{{ uniqid(more_entropy: true) }}"
    role="article"
    tabindex="0"
>
    <a class="absolute top-0 left-0 w-full h-full" href="{{ route('feed.details', $feedMessage) }}"
       wire:navigate.hover wire:show="!isDetailPage"></a>

    <x-profile-image-view class="w-16 h-16" :user="$feedMessage->user" />

    <div class="flex flex-col items-baseline w-full">
        <div class="flex gap-2 justify-between w-full">
            <div class="relative flex gap-1">
                <a
                    class="inline-flex items-center font-semibold whitespace-nowrap overflow-hidden"
                    href="{{ route('profile.details', $feedMessage->user) }}"
                >
                    {{ $feedMessage->user->username }}
                </a>

                <livewire:components.user.badge-shelf :user="$feedMessage->user" wire:key="{{ uniqid('badges-', true) }}" />
            </div>

            <a class="relative text-sm text-secondary whitespace-nowrap hover:underline" title="{{ $feedMessage->created_at }}"
               href="{{ route('feed.details', $feedMessage) }}">{{ $feedMessage->created_at->shortAbsoluteDiffForHumans() }}</a>
        </div>

        <div class="relative">
            <a
                class="inline-flex items-center text-sm text-secondary whitespace-nowrap overflow-hidden hover:underline"
                href="{{ route('profile.details', $feedMessage->user) }}"
                wire:navigate.hover
            >
                {{ '@' . $feedMessage->user->slug }}
            </a>
        </div>

        <div
            class="relative w-full max-w-prose"
            x-data="{
                hideBlur(el) {
                    el.classList.add('hidden')
                    this.configureHeight()
                },
                configureHeight() {
                    if ($refs.button.offsetHeight) {
                        $refs.content.style.height = $refs.button.offsetHeight + 'px'
                    } else {
                        $refs.content.style.height = null
                    }
                }
            }"
            x-init="configureHeight()"
            x-resize="configureHeight()"
        >
            <button
                class="absolute w-full pl-2 pr-2 pt-2 pb-2 backdrop-blur bg-tertiary text-sm rounded-md text-center {{ $feedMessage->is_spoiler || $feedMessage->is_nsfw ? '' : 'hidden' }}"
                x-on:click="hideBlur($el)"
                x-ref="button"
            >
                @if ($feedMessage->is_spoiler && !$feedMessage->is_nsfw)
                    <p>{{ __('This message contains spoilers — click to view') }}</p>
                @elseif (!$feedMessage->is_spoiler && $feedMessage->is_nsfw)
                    <p>{{ __('This message is NSFW — click to view') }}</p>
                @elseif ($feedMessage->is_spoiler && $feedMessage->is_nsfw)
                    <p>{{ __('This message is NSFW and contains spoilers — click to view') }}</p>
                @endif
            </button>

            <div x-ref="content" style="white-space: pre-wrap; overflow-wrap: break-word;"
                 x-html="displayContent"></div>

            @if ($this->linkPreview)
                {!! $this->linkPreview->render() !!}
            @endif
        </div>

        <div class="flex gap-2 justify-between w-full">
            <div class="relative flex justify-between">
                <x-square-button
                    title="{{ __('Reply') }}"
                    wire:click="reply"
                >
                    @svg('bubble_left_and_bubble_right_fill', 'fill-current', ['width' => '18'])
                    <p class="ml-2 mr-2">{{ $feedMessage->replies_count ?: '' }}</p>
                </x-square-button>

                <x-square-button
                    :class="$feedMessage->isReShared ? 'text-tint' : ''"
                    title="{{ __('Re-share') }}"
                    wire:click="reShare"
                >
                    @svg('square_and_arrow_up_on_square_fill', 'fill-current', ['width' => '18'])
                    <p class="ml-2 mr-2">{{ $feedMessage->re_shares_count ?: '' }}</p>
                </x-square-button>

                <x-square-button
                    :class="$isHearted ? 'text-tint' : ''"
                    title="{{ $isHearted ? __('Unlike') : __('Like')}}"
                    wire:click="toggleLike"
                >
                    @svg('heart_fill', 'fill-current', ['width' => '18'])
                    <p class="ml-2 mr-2">{{ $totalHearts->getCount() ?: '' }}</p>
                </x-square-button>
            </div>

            <div class="relative">
                {{-- More Options --}}
                <x-dropdown align="right" width="48">
                    <x-slot:trigger>
                        <x-square-button
                            title="{{ __('More') }}"
                        >
                            @svg('ellipsis', 'fill-current', ['width' => '18'])
                        </x-square-button>
                    </x-slot:trigger>

                    <x-slot:content>
                        <button
                            class="block w-full pl-4 pr-4 pt-2 pb-2 text-primary text-xs text-center font-semibold hover:bg-tertiary focus:bg-secondary"
                            wire:click="share"
                        >
                            {{ __('Share') }}
                        </button>

                        @auth
                            @if ($feedMessage->user->id == $authUser->id || $authUser->hasRole('superAdmin'))
                                <button
                                    class="block w-full pl-4 pr-4 pt-2 pb-2 text-primary text-xs text-center font-semibold hover:bg-tertiary focus:bg-secondary"
                                    wire:click="edit"
                                >
                                    {{ __('Edit') }}
                                </button>

                                <button
                                    class="block w-full pl-4 pr-4 pt-2 pb-2 text-red-500 text-xs text-center font-semibold hover:bg-tertiary focus:bg-secondary"
                                    wire:click="delete"
                                >
                                    {{ __('Delete') }}
                                </button>
                            @endif

                            @if ($feedMessage->user->id != $authUser->id)
                                <button
                                    class="block w-full pl-4 pr-4 pt-2 pb-2 text-red-500 text-xs text-center font-semibold hover:bg-tertiary focus:bg-secondary"
                                    wire:click="report"
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
</article>
