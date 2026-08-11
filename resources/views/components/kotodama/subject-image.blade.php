@props(['word'])

@php
    $square = 'w-24 h-24';
    $portrait = 'w-16 h-24';
    $squareRoundness = 'rounded-2xl';
    $artworkRoundness = 'rounded-lg';
@endphp

@switch($word->getSubjectKind())
    @case('literatures')
        <div class="relative {{ $portrait }} shrink-0">
            <svg class="relative block h-full w-full overflow-hidden" viewBox="0 0 112 160">
                <rect width="100%" height="100%" fill="var(--bg-secondary-color)" mask="url(#svg-mask-book-cover)" />

                <foreignObject width="112" height="160" mask="url(#svg-mask-book-cover)">
                    <img
                        class="w-full h-full object-cover"
                        src="{{ $word->getHintImageUrl() }}"
                        title="{{ $word->getSubjectTitle() }}"
                        alt="{{ $word->getSubjectTitle() }}"
                    >
                </foreignObject>

                <g opacity="0.40">
                    <use fill-opacity="0.03" fill="url(#svg-pattern-book-cover-1)" fill-rule="evenodd" xlink:href="#svg-rect-book-cover" />
                    <use fill-opacity="1" fill="url(#svg-linearGradient-book-cover-1)" fill-rule="evenodd" style="mix-blend-mode: lighten;" xlink:href="#svg-rect-book-cover" />
                    <use fill-opacity="1" fill="black" filter="url(#svg-filter-book-cover-1)" xlink:href="#svg-rect-book-cover" />
                </g>
            </svg>
        </div>
        @break
    @case('games')
        <x-picture class="shrink-0 {{ $square }} {{ $squareRoundness }} overflow-hidden" :border="true" :borderRoundness="$squareRoundness">
            <img
                class="w-full h-full object-cover"
                src="{{ $word->getHintImageUrl() }}"
                title="{{ $word->getSubjectTitle() }}"
                alt="{{ $word->getSubjectTitle() }}"
            >
        </x-picture>
        @break
    @case('characters')
    @case('people')
    @case('studios')
        <x-picture class="shrink-0 {{ $square }} rounded-full overflow-hidden" :border="true" borderRoundness="rounded-full">
            <img
                class="w-full h-full object-cover"
                src="{{ $word->getHintImageUrl() }}"
                title="{{ $word->getSubjectTitle() }}"
                alt="{{ $word->getSubjectTitle() }}"
            >
        </x-picture>
        @break
    @case('songs')
        <x-picture class="shrink-0 {{ $square }} {{ $artworkRoundness }} overflow-hidden" :border="true">
            <img
                class="w-full h-full object-cover"
                src="{{ $word->getHintImageUrl() }}"
                title="{{ $word->getSubjectTitle() }}"
                alt="{{ $word->getSubjectTitle() }}"
            >
        </x-picture>
        @break
    @default
        <x-picture class="shrink-0 {{ $portrait }} {{ $artworkRoundness }} overflow-hidden" :border="true">
            <img
                class="w-full h-full object-cover"
                src="{{ $word->getHintImageUrl() }}"
                title="{{ $word->getSubjectTitle() }}"
                alt="{{ $word->getSubjectTitle() }}"
            >
        </x-picture>
@endswitch
