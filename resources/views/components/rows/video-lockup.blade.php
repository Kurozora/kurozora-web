@props(['animes' => [], 'games' => [], 'isRow' => true, 'safeAreaInsetEnabled' => true, 'marksLibrary' => false])

@if (!empty($animes))
    <x-rows.container lockup="video" :is-row="$isRow" :safe-area-inset-enabled="$safeAreaInsetEnabled" {{ $attributes }}>
        @foreach ($animes as $anime)
            <x-lockups.video-lockup :anime="$anime" :is-row="$isRow" :in-library="$marksLibrary && $anime->library->isNotEmpty()" />
        @endforeach
    </x-rows.container>
@elseif (!empty($games))
    <x-rows.container lockup="video" :is-row="$isRow" :safe-area-inset-enabled="$safeAreaInsetEnabled" {{ $attributes }}>
        @foreach ($games as $game)
            <x-lockups.video-lockup :game="$game" :is-row="$isRow" :in-library="$marksLibrary && $game->library->isNotEmpty()" />
        @endforeach
    </x-rows.container>
@endif
