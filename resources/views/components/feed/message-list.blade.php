@if ($fragment)
    <div data-feed-page data-feed-next="{{ $nextCursor }}" data-feed-latest="{{ $latestId }}">
        @foreach ($feedMessages as $feedMessage)
            <x-feed.message-lockup :feed-message="$feedMessage" />
        @endforeach
    </div>
@else
    <div
        data-feed-list
        data-feed-url="{{ route('feed.section', [], false) }}"
        data-feed-latest="{{ $latestId }}"
        data-feed-label-one="{{ __('Show :x message', ['x' => ':x']) }}"
        data-feed-label-many="{{ __('Show :x messages', ['x' => ':x']) }}"
    >
        <div class="hidden text-center" data-feed-newer>
            <button class="w-full pt-4 pb-4 font-semibold text-tint border-b border-primary hover:bg-tertiary focus:bg-secondary" data-feed-show-newer></button>
        </div>

        <div class="flex flex-col" style="content-visibility: auto;" data-feed-messages>
            @foreach ($feedMessages as $feedMessage)
                <x-feed.message-lockup :feed-message="$feedMessage" />
            @endforeach
        </div>

        @if ($nextCursor)
            <div data-feed-more data-feed-cursor="{{ $nextCursor }}"></div>
        @endif
    </div>
@endif
