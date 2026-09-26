@props([
    'lockup' => 'small',
    'kind' => \App\Enums\UserLibraryKind::Anime,
    'isRow' => true,
    'count' => null,
    'safeAreaInsetEnabled' => true,
])

@php
    $itemCount = $count ?? ($isRow ? ($lockup === 'person' ? 15 : 5) : 25);
@endphp

<x-rows.container :lockup="$lockup" :is-row="$isRow" :safe-area-inset-enabled="$safeAreaInsetEnabled" class="justify-start" {{ $attributes }}>
    @for ($index = 0; $index < $itemCount; $index++)
        @switch($lockup)
            @case('episode')
                <x-skeletons.episode-lockup-item :is-row="$isRow" />
                @break
            @case('video')
                <x-skeletons.video-lockup-item :is-row="$isRow" />
                @break
            @case('upcoming')
                <x-skeletons.upcoming-lockup-item />
                @break
            @case('medium')
                <x-skeletons.medium-lockup-item />
                @break
            @case('person')
                <x-skeletons.person-lockup-item :is-row="$isRow" />
                @break
            @case('music')
                <x-skeletons.music-lockup-item :is-row="$isRow" />
                @break
            @case('recap')
                <x-skeletons.recap-lockup-item />
                @break
            @case('studio')
                <x-skeletons.studio-lockup-item :is-row="$isRow" />
                @break
            @case('platform')
                <x-skeletons.platform-lockup-item :is-row="$isRow" />
                @break
            @case('season')
                <x-skeletons.season-lockup-item :is-row="$isRow" />
                @break
            @case('user')
                <x-skeletons.user-lockup-item :is-row="$isRow" />
                @break
            @case('review')
                <x-skeletons.review-lockup-item :is-row="$isRow" />
                @break
            @case('media-rating')
                <x-skeletons.media-rating-lockup-item :is-row="$isRow" />
                @break
            @case('achievement')
                <x-skeletons.achievement-lockup-item />
                @break
            @case('cast')
                <x-skeletons.cast-lockup-item :is-row="$isRow" />
                @break
            @case('trailer')
                <x-skeletons.trailer-lockup-item :is-row="$isRow" />
                @break
            @case('theme')
                <x-skeletons.theme-lockup-item />
                @break
            @default
                <x-skeletons.small-lockup-item :kind="$kind" :is-row="$isRow" />
        @endswitch
    @endfor
</x-rows.container>
