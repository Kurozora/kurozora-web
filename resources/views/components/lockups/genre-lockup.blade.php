@props(['genre'])

@php
    $backgroundColor = match ($genre->color) {
        '#ffffff' => 'background: linear-gradient(-45deg, rgb(56, 62, 87) 22%, rgb(98, 112, 170) 88%)',
        default => 'background-color: ' . $genre->color
    };
@endphp

<a class="relative" href="{{ route('genres.details', ['genre' => $genre]) }}">
    <div class="flex">
        <picture class="relative rounded-lg overflow-hidden" style="{{ $backgroundColor }};">
            <img class="pt-3 pr-3 pb-3 pl-3 aspect-square lazyload" data-sizes="auto" data-src="{{ $genre->symbol_image_url ?? asset('images/static/icon/logo.webp') }}" alt="{{ $genre->name }} Symbol" title="{{ $genre->name }}">

            <div
                class="pr-3 pl-3 pt-4 pb-4 bg-black/30 backdrop-blur text-center"
                style="height: 12895;"
            >
                <p class="text-white font-bold leading-tight line-clamp-1">{{ $genre->name }}</p>
                <p class="text-sm text-white/90 leading-tight line-clamp-2">{{ $genre->description }}</p>
            </div>

            <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
        </picture>
    </div>
</a>
