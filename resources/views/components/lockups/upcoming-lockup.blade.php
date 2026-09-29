@props(['anime' => null, 'manga' => null, 'game' => null])

@if (!empty($anime))
    <x-lockups.upcoming-body :model="$anime" :href="route('anime.details', $anime)">
        @auth
            <x-reminder-button :model="$anime" />
        @else
            <x-library-button :model="$anime" />
        @endauth
    </x-lockups.upcoming-body>
@elseif (!empty($game))
    <x-lockups.upcoming-body :model="$game" :href="route('games.details', $game)">
        @auth
{{--            <x-reminder-button :model="$game" />--}}
            <x-library-button :model="$game" />
        @else
            <x-library-button :model="$game" />
        @endauth
    </x-lockups.upcoming-body>
@elseif (!empty($manga))
    <x-lockups.upcoming-body :model="$manga" :href="route('manga.details', $manga)">
        @auth
{{--            <x-reminder-button :model="$manga" />--}}
            <x-library-button :model="$manga" />
        @else
            <x-library-button :model="$manga" />
        @endauth
    </x-lockups.upcoming-body>
@endif
