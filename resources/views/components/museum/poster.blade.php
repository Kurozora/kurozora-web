@props(['kind' => \App\Enums\UserLibraryKind::Anime])

@switch($kind)
@case(\App\Enums\UserLibraryKind::Manga)
    <div class="relative w-28 h-40">
        <svg class="relative block h-full w-full overflow-hidden">
            <rect data-museum-poster-bg width="100%" height="100%" mask="url(#svg-mask-book-cover)" />

            <foreignObject width="112" height="160" mask="url(#svg-mask-book-cover)">
                <img data-museum-poster-bg class="w-full h-full object-cover lazyload" data-sizes="auto" alt="">
            </foreignObject>

            <g opacity="0.40">
                <use fill-opacity="0.03" fill="url(#svg-pattern-book-cover-1)" fill-rule="evenodd" xlink:href="#svg-rect-book-cover" />
                <use fill-opacity="1" fill="url(#svg-linearGradient-book-cover-1)" fill-rule="evenodd" style="mix-blend-mode: lighten;" xlink:href="#svg-rect-book-cover" />
                <use fill-opacity="1" fill="black" filter="url(#svg-filter-book-cover-1)" xlink:href="#svg-rect-book-cover" />
            </g>
        </svg>

        <a data-museum-poster-link class="absolute top-0 left-0 w-full h-full" wire:navigate></a>
    </div>
    @break
@case(\App\Enums\UserLibraryKind::Game)
    <div class="relative w-28 h-28">
        <picture data-museum-poster-bg class="relative block w-full h-full overflow-hidden rounded-3xl">
            <img class="w-full h-full object-cover lazyload" data-sizes="auto" alt="">

            <div class="pointer-events-none absolute top-0 left-0 w-full h-full border border-solid border-black/20 rounded-3xl"></div>
        </picture>

        <a data-museum-poster-link class="absolute top-0 left-0 w-full h-full" wire:navigate></a>
    </div>
    @break
@default
    <div class="relative w-28 h-40">
        <picture data-museum-poster-bg class="relative block w-full h-full overflow-hidden rounded-lg">
            <img class="w-full h-full object-cover lazyload" data-sizes="auto" alt="">

            <div class="pointer-events-none absolute top-0 left-0 w-full h-full border border-solid border-black/20 rounded-lg"></div>
        </picture>

        <a data-museum-poster-link class="absolute top-0 left-0 w-full h-full" wire:navigate></a>
    </div>
@endswitch
