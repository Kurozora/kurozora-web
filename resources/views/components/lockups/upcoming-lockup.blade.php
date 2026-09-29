@props(['anime' => null, 'manga' => null, 'game' => null])

@if (!empty($anime))
    <x-lockups.upcoming-body :model="$anime" :href="route('anime.details', $anime)">
        @auth
            @if (auth()->user()->is_subscribed)
                <x-reminder-button :anime="$anime" />
            @else
                <x-library-button :model="$anime" />
            @endif
        @else
            <x-library-button :model="$anime" />
        @endauth
    </x-lockups.upcoming-body>
@elseif (!empty($game))
    <x-lockups.upcoming-body :model="$game" :href="route('games.details', $game)">
        @auth
{{--            @if (auth()->user()->is_subscribed)--}}
{{--                <livewire:game.reminder-button :game="$game" wire:key="{{ uniqid($game->id, true) }}" />--}}
{{--            @else--}}
            <x-library-button :model="$game" />
{{--            @endif--}}
        @else
            <x-library-button :model="$game" />
        @endauth
    </x-lockups.upcoming-body>
@elseif (!empty($manga))
    <x-lockups.upcoming-body :model="$manga" :href="route('manga.details', $manga)">
        @auth
{{--            @if (auth()->user()->is_subscribed)--}}
{{--                <livewire:manga.reminder-button :manga="$manga" wire:key="{{ uniqid($manga->id, true) }}" />--}}
{{--            @else--}}
            <x-library-button :model="$manga" />
{{--            @endif--}}
        @else
            <x-library-button :model="$manga" />
        @endauth
    </x-lockups.upcoming-body>
@endif
