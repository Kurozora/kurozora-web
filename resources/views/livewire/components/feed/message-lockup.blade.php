@php
    $authUser = auth()->user();
    $displayMessage = $this->displayMessage;

    $isSimpleReShareWrapper = $displayMessage->id !== $feedMessage->id;
    $resharer = $isSimpleReShareWrapper ? $feedMessage->user : null;
    $isSelfReShare = $resharer && $authUser && $resharer->id === $authUser->id;

    $isQuoteReShare = $displayMessage->is_reshare
        && trim((string) $displayMessage->content) !== ''
        && $displayMessage->parentMessage;
    $quoteParent = $isQuoteReShare ? $displayMessage->parentMessage : null;

    $totalHearts = $displayMessage->viaLoveReactant()->getReactionCounterOfType(\App\Enums\FeedVoteType::Heart()->description);
    $isHearted = $authUser && $authUser->getCurrentHeartValueFor($displayMessage) == \App\Enums\FeedVoteType::Heart;
@endphp

<article
    class="relative flex flex-col pr-4 pl-4 pt-4 pb-2 border-b border-primary"
    x-data="{
        displayContent: '',

        init() {
            const content = @js($displayMessage->content)

            this.displayContent = markdown.parse(content.replace(/(?:https?|http):\/\/[\n\S]+$/, '').trim(), 0, null, true)
        }
    }"
    x-init="init()"
    role="article"
    tabindex="0"
>
    <a class="absolute top-0 left-0 w-full h-full" href="{{ route('feed.details', $displayMessage) }}"
       wire:navigate.hover wire:show="!isDetailPage"></a>

    @if ($resharer)
    <a
        class="relative flex items-center gap-1 mb-1 ml-12 pl-2 text-xs text-secondary hover:underline z-10"
        href="{{ route('profile.details', $resharer) }}"
        wire:navigate.hover
    >
        @svg('square_and_arrow_up_on_square_fill', 'fill-current', ['width' => '12'])

        <span>{{ $isSelfReShare ? __('You re-shared') : __('Re-shared by :user', ['user' => '@' . $resharer->slug]) }}</span>
    </a>
    @endif

    <div class="flex flex-row gap-2">
        <x-profile-image-view class="w-12 h-12" :user="$displayMessage->user" />

        <div class="flex flex-col w-full">
            <div class="flex gap-2 justify-between items-baseline w-full">
                <div class="relative flex gap-1">
                    <a
                        class="inline-flex items-center font-semibold whitespace-nowrap overflow-hidden"
                        href="{{ route('profile.details', $displayMessage->user) }}"
                    >
                        {{ $displayMessage->user->username }}
                    </a>

                    <livewire:components.user.badge-shelf :user="$displayMessage->user" wire:key="{{ uniqid('badges-', true) }}" />
                </div>

                <a class="relative text-sm text-secondary whitespace-nowrap hover:underline" title="{{ $displayMessage->created_at }}"
                   href="{{ route('feed.details', $displayMessage) }}">{{ $displayMessage->created_at->shortAbsoluteDiffForHumans() }}</a>
            </div>

            <div class="relative flex">
                <a
                    class="inline-flex items-center text-sm text-secondary whitespace-nowrap overflow-hidden hover:underline"
                    href="{{ route('profile.details', $displayMessage->user) }}"
                    wire:navigate.hover
                >
                    {{ '@' . $displayMessage->user->slug }}
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
                    class="absolute w-full pl-2 pr-2 pt-2 pb-2 backdrop-blur bg-tertiary text-sm rounded-md text-center {{ $displayMessage->is_spoiler || $displayMessage->is_nsfw ? '' : 'hidden' }}"
                    x-on:click="hideBlur($el)"
                    x-ref="button"
                >
                    @if ($displayMessage->is_spoiler && !$displayMessage->is_nsfw)
                        <p>{{ __('This message contains spoilers — click to view') }}</p>
                    @elseif (!$displayMessage->is_spoiler && $displayMessage->is_nsfw)
                        <p>{{ __('This message is NSFW — click to view') }}</p>
                    @elseif ($displayMessage->is_spoiler && $displayMessage->is_nsfw)
                        <p>{{ __('This message is NSFW and contains spoilers — click to view') }}</p>
                    @endif
                </button>

                <div
                    x-ref="content" style="white-space: pre-wrap; overflow-wrap: break-word;"
                    x-html="displayContent"
                ></div>

                @if ($this->linkPreview)
                    {!! $this->linkPreview->render() !!}
                @endif

                @if ($quoteParent)
                    <a class="relative flex flex-col mt-2 p-3 border border-primary rounded-lg bg-secondary hover:bg-tertiary z-10"
                       href="{{ route('feed.details', $quoteParent) }}"
                       wire:navigate.hover>
                        <div class="flex items-center gap-2 mb-1">
                            <picture
                                class="relative shrink-0 w-6 h-6 rounded-full overflow-hidden"
                                style="background-color: {{ $quoteParent->user->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
                            >
                                <img
                                    class="w-full h-full object-cover"
                                    src="{{ $quoteParent->user->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) }}"
                                    alt="{{ $quoteParent->user->username }} Profile Image"
                                    width="24" height="24"
                                >
                            </picture>

                            <span class="font-semibold text-sm">{{ $quoteParent->user->username }}</span>
                            <span class="text-xs text-secondary">{{ '@' . $quoteParent->user->slug }}</span>
                            <span class="text-xs text-secondary">·</span>
                            <span class="text-xs text-secondary">{{ $quoteParent->created_at->shortAbsoluteDiffForHumans() }}</span>
                        </div>

                        <div class="text-sm" style="white-space: pre-wrap; overflow-wrap: break-word;">{{ $quoteParent->content }}</div>
                    </a>
                @endif
            </div>

            <div class="flex gap-2 justify-between w-full">
                <div class="relative flex justify-between">
                    <x-square-button
                        title="{{ __('Reply') }}"
                        wire:click="reply"
                    >
                        @svg('bubble_left_and_bubble_right_fill', 'fill-current', ['width' => '18'])
                        <p class="ml-2 mr-2">{{ $displayMessage->replies_count ?: '' }}</p>
                    </x-square-button>

                    <div class="relative">
                        <x-dropdown align="left" width="48">
                            <x-slot:trigger>
                                <x-square-button
                                    :class="$displayMessage->isReShared ? 'text-tint' : ''"
                                    title="{{ __('Re-share') }}"
                                >
                                    @svg('square_and_arrow_up_on_square_fill', 'fill-current', ['width' => '18'])

                                    <p class="ml-2 mr-2">{{ $displayMessage->re_shares_count ?: '' }}</p>
                                </x-square-button>
                            </x-slot:trigger>

                            <x-slot:content>
                                <button
                                    class="block w-full pl-4 pr-4 pt-2 pb-2 text-primary text-xs text-center font-semibold hover:bg-tertiary focus:bg-secondary"
                                    wire:click="toggleSimpleReShare"
                                >
                                    {{ $displayMessage->isReShared ? __('Undo Re-share') : __('Re-share') }}
                                </button>

                                <button
                                    class="block w-full pl-4 pr-4 pt-2 pb-2 text-primary text-xs text-center font-semibold hover:bg-tertiary focus:bg-secondary"
                                    wire:click="quote"
                                >
                                    {{ __('Quote') }}
                                </button>

                                <a
                                    class="block w-full pl-4 pr-4 pt-2 pb-2 text-primary text-xs text-center font-semibold hover:bg-tertiary focus:bg-secondary"
                                    href="{{ route('feed.activity', $displayMessage) }}"
                                    wire:navigate.hover
                                >
                                    {{ __('View post activity') }}
                                </a>
                            </x-slot:content>
                        </x-dropdown>
                    </div>

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
                                @if ($displayMessage->user->id == $authUser->id || $authUser->hasRole('superAdmin'))
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

                                @if ($displayMessage->user->id != $authUser->id)
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
    </div>
</article>
