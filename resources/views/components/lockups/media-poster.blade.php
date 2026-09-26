@props(['model', 'kind'])

@php
    $backgroundColor = $model->getFirstMedia(\App\Enums\MediaCollection::Poster)?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)';
    $posterUrl = $model->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp');
@endphp

@switch($kind)
    @case(\App\Enums\UserLibraryKind::Game)
        <picture
            class="relative shrink-0 w-28 h-28 rounded-3xl overflow-hidden"
            style="background-color: {{ $backgroundColor }};"
        >
            <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $posterUrl }}" alt="{{ $model->title }} Poster" title="{{ $model->title }}">

            <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-3xl"></div>
        </picture>
        @break
    @case(\App\Enums\UserLibraryKind::Manga)
        <svg class="relative shrink-0 w-28 h-40 overflow-hidden">
            <rect width="100%" height="100%" fill="{{ $backgroundColor }}" mask="url(#svg-mask-book-cover)" />

            <foreignObject width="112" height="160" mask="url(#svg-mask-book-cover)">
                <img
                    class="h-full w-full object-cover lazyload"
                    style="background-color: {{ $backgroundColor }};"
                    data-sizes="auto"
                    data-src="{{ $posterUrl }}"
                    alt="{{ $model->title }} Poster"
                    title="{{ $model->title }}"
                >
            </foreignObject>

            <g opacity="0.40">
                <use fill-opacity="0.03" fill="url(#svg-pattern-book-cover-1)" fill-rule="evenodd" xlink:href="#svg-rect-book-cover" />
                <use fill-opacity="1" fill="url(#svg-linearGradient-book-cover-1)" fill-rule="evenodd" style="mix-blend-mode: lighten;" xlink:href="#svg-rect-book-cover" />
                <use fill-opacity="1" fill="black" filter="url(#svg-filter-book-cover-1)" xlink:href="#svg-rect-book-cover" />
            </g>
        </svg>
        @break
    @default
        <picture
            class="relative shrink-0 w-28 h-40 rounded-lg overflow-hidden"
            style="background-color: {{ $backgroundColor }};"
        >
            <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $posterUrl }}" alt="{{ $model->title }} Poster" title="{{ $model->title }}">

            <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
        </picture>
@endswitch
