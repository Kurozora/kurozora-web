@props(['word'])

@switch($word->getSubjectKind())
    @case('literatures')
        <div class="relative w-28 h-40 shrink-0">
            <svg class="relative block h-full w-full overflow-hidden">
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
        <x-picture class="shrink-0 w-28 h-28 rounded-3xl overflow-hidden" :border="true" borderRoundness="rounded-3xl">
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
        <x-picture class="shrink-0 w-28 h-28 rounded-full overflow-hidden" :border="true" borderRoundness="rounded-full">
            <img
                class="w-full h-full object-cover"
                src="{{ $word->getHintImageUrl() }}"
                title="{{ $word->getSubjectTitle() }}"
                alt="{{ $word->getSubjectTitle() }}"
            >
        </x-picture>
        @break
    @case('songs')
        <x-picture class="shrink-0 w-28 h-28 rounded-lg overflow-hidden" :border="true">
            <img
                class="w-full h-full object-cover"
                src="{{ $word->getHintImageUrl() }}"
                title="{{ $word->getSubjectTitle() }}"
                alt="{{ $word->getSubjectTitle() }}"
            >
        </x-picture>
        @break
    @default
        <x-picture class="shrink-0 w-28 h-40 rounded-lg overflow-hidden" :border="true">
            <img
                class="w-full h-full object-cover"
                src="{{ $word->getHintImageUrl() }}"
                title="{{ $word->getSubjectTitle() }}"
                alt="{{ $word->getSubjectTitle() }}"
            >
        </x-picture>
@endswitch
