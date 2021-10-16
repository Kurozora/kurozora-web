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
            <img class="p-3 lazyload" data-sizes="auto" data-src="{{ $genre->symbol_image_url ?? asset('images/static/icon/logo.png') }}" alt="{{ $genre->name }} Symbol" title="{{ $genre->name }}">

            <div class="h-[95px] p-3 py-5 bg-black/30 backdrop-blur text-center">
                <p class="text-white font-bold leading-tight line-clamp-1">{{ $genre->name }}</p>
                <p class="text-sm text-white/90 leading-tight line-clamp-2">{{ $genre->description }}</p>
            </div>

            <div class="absolute top-0 left-0 h-full w-full border-[1px] border-solid border-black/20 rounded-lg"></div>
        </picture>
    </div>
</a>
