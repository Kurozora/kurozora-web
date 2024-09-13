@props(['genre' => null, 'theme' => null, 'href' => '', 'title' => '', 'backgroundColor' => 'transparent', 'backgroundImage' => ''])

@if (!empty($genre))
    <a href="{{ route('genres.details', $genre) }}" wire:navigate class="relative pb-2 snap-normal snap-center">
        <div
            class="flex justify-center w-64 h-40 rounded-lg"
            style="background: linear-gradient(-180deg, {{ $genre->background_color_1 }} 32%, {{ $genre->background_color_2 }} 98%);"
        >
            <picture
                class="relative"
                style="background: url({{ asset('images/static/patterns/genre_pattern.svg') }}) no-repeat center; background-size: cover;"
            >
                <img class="h-full m-auto lazyload" data-sizes="auto" data-src="{{ $genre->getFirstMediaFullUrl(\App\Enums\MediaCollection::Symbol()) ?? asset('images/static/icon/logo.webp') }}" alt="{{ $genre->name }} Symbol" title="{{ $genre->name }}">

                <div
                    class="absolute w-full h-full top-0 left-0"
                    style="background: url({{ asset('images/static/patterns/grain.svg') }})"
                ></div>
            </picture>
        </div>

        @if (!empty($genre->name))
            <p class="pt-3">{{ $genre->name }}</p>
        @endif
    </a>
@elseif (!empty($theme))
    <a href="{{ route('themes.details', $theme) }}" wire:navigate class="relative pb-2 snap-normal snap-center">
        <div
            class="flex justify-center w-64 h-40 rounded-lg"
            style="background: linear-gradient(-180deg, {{ $theme->background_color_1 }} 32%, {{ $theme->background_color_2 }} 98%);"
        >
            <picture
                class="relative"
                style="background: url({{ asset('images/static/patterns/genre_pattern.svg') }}) no-repeat center; background-size: cover;"
            >
                <img class="h-full m-auto lazyload" data-sizes="auto" data-src="{{ $theme->getFirstMediaFullUrl(\App\Enums\MediaCollection::Symbol()) ?? asset('images/static/icon/logo.webp') }}" alt="{{ $theme->name }} Symbol" title="{{ $theme->name }}">

                <div
                    class="absolute w-full h-full top-0 left-0"
                    style="background: url({{ asset('images/static/patterns/grain.svg') }})"
                ></div>
            </picture>
        </div>

        @if (!empty($theme->name))
            <p class="pt-3">{{ $theme->name }}</p>
        @endif
    </a>
@else
    <a href="{{ $href }}" wire:navigate class="relative pb-2 snap-normal snap-center">
        <div class="flex justify-center w-64 h-40 rounded-lg border-2 border-solid border-black/5" style="background-color: {{ $backgroundColor }};">
            <picture class="relative">
                <img class="h-full m-auto lazyload" data-sizes="auto" data-src="{{ $backgroundImage }}" alt="{{ $title }} Symbol" title="{{ $title }}">
            </picture>
        </div>

        @if (!empty($title))
            <p class="pt-3">{{ $title }}</p>
        @endif
    </a>
@endif
