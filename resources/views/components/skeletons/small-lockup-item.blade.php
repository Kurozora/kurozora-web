@props(['kind' => \App\Enums\UserLibraryKind::Anime, 'isRow' => true])

@php
    $class = $isRow ? 'lockup-media' : 'lockup-media-grid';
@endphp

<div {{ $attributes->merge(['class' => $class]) }}>
    <div class="flex flex-nowrap gap-2">
        @switch($kind)
            @case(\App\Enums\UserLibraryKind::Game)
                <p class="shrink-0 w-28 h-28 bg-secondary rounded-3xl"></p>
                @break
            @case(\App\Enums\UserLibraryKind::Manga)
                <svg class="relative shrink-0 w-28 h-40 overflow-hidden">
                    <rect width="100%" height="100%" fill="var(--bg-secondary-color)" mask="url(#svg-mask-book-cover)" />
                </svg>
                @break
            @default
                <p class="shrink-0 w-28 h-40 bg-secondary rounded-lg"></p>
        @endswitch

        <div class="flex flex-col w-full gap-2 justify-between">
            <div class="flex flex-col gap-1">
                <p class="bg-secondary rounded-md" style="width: 100%; height: 20px"></p>
                <p class="bg-secondary rounded-md" style="width: 75%; height: 16px"></p>
                <p class="bg-secondary rounded-md" style="width: 40%; height: 16px"></p>
                <p class="bg-secondary rounded-md" style="width: 96px; height: 20px"></p>
            </div>

            <p class="w-24 bg-secondary rounded-full sm:w-32" style="height: 32px"></p>
        </div>
    </div>
</div>
