@props(['theme'])

<a class="relative" href="{{ route('themes.details', ['theme' => $theme]) }}">
    <div class="flex">
        <picture
            class="relative rounded-lg overflow-hidden"
            style="background: url({{ asset('images/static/patterns/grain.svg') }}), linear-gradient(-180deg, {{ $theme->background_color_1 }} 32%, {{ $theme->background_color_2 }} 98%);"
        >
            <div
                class="relative"
                style="background-color: rgba(255, 255, 255, 0.25);"
            >
                <img
                    class="pt-3 pr-3 pb-3 pl-3 aspect-square lazyload"
                    style="background: url({{ asset('images/static/patterns/genre_pattern.svg') }})"
                    data-sizes="auto"
                    data-src="{{ $theme->getFirstMediaFullUrl(\App\Enums\MediaCollection::Symbol()) ?? asset('images/static/placeholders/empty.webp') }}"
                    alt="{{ $theme->name }} Symbol"
                    title="{{ $theme->name }}"
                >
                <div
                    class="absolute w-full h-full top-0 left-0"
                    style="background: url({{ asset('images/static/patterns/grain.svg') }})"
                ></div>
            </div>

            <div
                class="flex flex-col items-start pr-4 pl-4 pt-2 pb-2 border-t-4 uppercase"
                style="border-color: {{ $theme->background_color_2 }};"
            >
                <div>
                    <p
                        class="font-bold text-2xl line-clamp-1"
                        style="color: {{ $theme->text_color_1 }};"
                    >{{ $theme->name }}</p>
                    <x-hr
                        class="mt-1 mb-2 rounded border"
                        style="border-color: {{ $theme->text_color_1 }};"
                    />
                </div>
                <p
                    class="text-sm line-clamp-3"
                    style="color: {{ $theme->text_color_2 }};"
                >{{ $theme->description }}</p>
            </div>

            <div class="absolute top-0 left-0 h-full w-full rounded-lg"></div>
        </picture>
    </div>
</a>
