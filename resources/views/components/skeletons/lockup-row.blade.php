@props([
    'lockup' => 'small',
    'kind' => \App\Enums\UserLibraryKind::Anime,
    'isRow' => true,
    'count' => null,
    'safeAreaInsetEnabled' => true,
])

@php
    $itemCount = $count ?? ($isRow ? ($lockup === 'person' ? 15 : 5) : 25);

    $spacerCount = match ($lockup) {
        'person' => 18,
        'music', 'medium' => 8,
        default => 6,
    };

    $spacerWidth = match ($lockup) {
        'person' => 'w-28',
        'recap' => 'w-[98%]',
        'medium' => 'w-[98%] max-w-[16rem]',
        'music' => 'w-[98%] sm:w-64',
        'review', 'cast' => 'w-[98%] sm:w-96',
        'achievement' => 'w-[98%] sm:w-72',
        default => 'w-[98%] sm:w-80',
    };

    $class = $isRow ? 'flex-nowrap overflow-hidden' : 'flex-wrap';

    if ($isRow && $safeAreaInsetEnabled) {
        $class .= ' xl:safe-area-inset-scroll';
    }
@endphp

<div {{ $attributes->merge(['class' => 'flex gap-4 justify-start pl-4 pr-4 ' . $class]) }}>
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
            @default
                <x-skeletons.small-lockup-item :kind="$kind" :is-row="$isRow" />
        @endswitch
    @endfor

    @unless ($isRow)
        @for ($index = 0; $index < $spacerCount; $index++)
            <div class="{{ $spacerWidth }} flex-grow"></div>
        @endfor
    @endunless
</div>
