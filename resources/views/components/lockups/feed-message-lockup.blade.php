@props(['feedMessage'])

@php
    $totalHearts = $feedMessage->viaLoveReactant()->getReactionCounterOfType(\App\Enums\FeedVoteType::Heart()->description);
    $authUser = auth()->user();
    $isReShared = $authUser && $feedMessage->reShares()->where('user_id', $authUser->id)->exists();
    $isHearted = $authUser && $authUser->getCurrentHeartValueFor($feedMessage) == \App\Enums\FeedVoteType::Heart
@endphp

<div
    class="relative flex flex-row gap-2 pr-2 pl-2 pt-2 bg-gray-100 rounded-xl"
    x-data="{}"
    wire:key="{{ md5($feedMessage->created_at) }}"
>
    <x-profile-image-view class="w-16 h-16" :user="$feedMessage->user" />

    <div class="flex flex-col items-baseline w-full overflow-hidden">
        <div class="flex gap-2 justify-between w-full">
            <div class="flex gap-1 overflow-hidden">
                <x-simple-link class="text-sm font-semibold text-gray-500 whitespace-nowrap overflow-hidden" href="{{ route('profile.details', $feedMessage->user) }}">{{ $feedMessage->user->username }}</x-simple-link>
                @if ($feedMessage->user->is_verified)
                    @svg('checkmark_seal_fill', 'text-orange-500 fill-current', ['width' => 18, 'style' => 'min-width: 18px;'])
                @endif
                @if ($feedMessage->user->is_pro || $feedMessage->user->is_subscribed)
                    <x-pro-badge />
                @endif
            </div>

            <p class="text-sm text-gray-500 whitespace-nowrap" title="{{ $feedMessage->created_at }}">{{ $feedMessage->created_at->shortAbsoluteDiffForHumans() }}</p>
        </div>

        <div class="w-full" style="word-break: break-word;">{!! $feedMessage->content_html !!}</div>

        <div class="flex gap-2 justify-between w-full">
            <div class="flex justify-between">
                <x-square-button>
                    @svg('bubble_left_and_bubble_right_fill', 'fill-current', ['width' => '18'])
                    <p class="ml-2 mr-2">{{ $feedMessage->replies()->count() }}</p>
                </x-square-button>

                <x-square-button :class="$isReShared ? 'text-green-500' : ''">
                    @svg('square_and_arrow_up_on_square_fill', 'fill-current', ['width' => '18'])
                    <p class="ml-2 mr-2">{{ $feedMessage->reshares()->count() }}</p>
                </x-square-button>

                <x-square-button :class="$isHearted ? 'text-red-500' : ''">
                    @svg('heart_fill', 'fill-current', ['width' => '18'])
                    <p class="ml-2 mr-2">{{ $totalHearts->getCount() }}</p>
                </x-square-button>
            </div>

            <div>
                <x-square-button>
                    @svg('ellipsis', 'fill-current', ['width' => '18'])
                </x-square-button>
            </div>
        </div>
    </div>
</div>