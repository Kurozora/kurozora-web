@props(['anime' => null, 'manga' => null, 'game' => null])

@if (!empty($anime))
    <x-lockups.upcoming-body :model="$anime" :href="route('anime.details', $anime)">
        @auth
            @if (auth()->user()->is_subscribed)
                <livewire:anime.reminder-button :anime="$anime" wire:key="{{ uniqid($anime->id, true) }}" />
            @else
                <livewire:components.library-button :model="$anime" wire:key="{{ uniqid($anime->id, true) }}" />
            @endif
        @else
            <livewire:components.library-button :model="$anime" wire:key="{{ uniqid($anime->id, true) }}" />
        @endauth
    </x-lockups.upcoming-body>
@elseif (!empty($game))
    <x-lockups.upcoming-body :model="$game" :href="route('games.details', $game)">
        @auth
{{--            @if (auth()->user()->is_subscribed)--}}
{{--                <livewire:game.reminder-button :game="$game" wire:key="{{ uniqid($game->id, true) }}" />--}}
{{--            @else--}}
            <livewire:components.library-button :model="$game" wire:key="{{ uniqid($game->id, true) }}" />
{{--            @endif--}}
        @else
            <livewire:components.library-button :model="$game" wire:key="{{ uniqid($game->id, true) }}" />
        @endauth
    </x-lockups.upcoming-body>
@elseif (!empty($manga))
    <x-lockups.upcoming-body :model="$manga" :href="route('manga.details', $manga)">
        @auth
{{--            @if (auth()->user()->is_subscribed)--}}
{{--                <livewire:manga.reminder-button :manga="$manga" wire:key="{{ uniqid($manga->id, true) }}" />--}}
{{--            @else--}}
            <livewire:components.library-button :model="$manga" wire:key="{{ uniqid($manga->id, true) }}" />
{{--            @endif--}}
        @else
            <livewire:components.library-button :model="$manga" wire:key="{{ uniqid($manga->id, true) }}" />
        @endauth
    </x-lockups.upcoming-body>
@endif
