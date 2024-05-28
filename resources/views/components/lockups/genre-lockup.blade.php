@props(['genre'])

<a class="relative" href="{{ route('genres.details', ['genre' => $genre]) }}" wire:navigate>
    <div class="flex">
        <picture
            class="relative rounded-lg overflow-hidden"
            style="background: url({{ asset('images/static/patterns/grain.svg') }}), linear-gradient(-180deg, {{ $genre->background_color_1 }} 32%, {{ $genre->background_color_2 }} 98%);"
        >
            <div
                class="relative"
                style="background-color: rgba(255, 255, 255, 0.25);"
            >
                <img
                    class="pt-3 pr-3 pb-3 pl-3 aspect-square lazyload"
                    style="background: url({{ asset('images/static/patterns/genre_pattern.svg') }})"
                    data-sizes="auto"
                    data-src="{{ $genre->getFirstMediaFullUrl(\App\Enums\MediaCollection::Symbol()) ?? asset('images/static/placeholders/empty.webp') }}"
                    alt="{{ $genre->name }} Symbol"
                    title="{{ $genre->name }}"
                >
                <div
                    class="absolute w-full h-full top-0 left-0"
                    style="background: url({{ asset('images/static/patterns/grain.svg') }})"
                ></div>
            </div>

            <div
                class="flex flex-col items-start pr-4 pl-4 pt-2 pb-2 border-t-4 uppercase"
                style="border-color: {{ $genre->background_color_2 }};"
            >
                <div class="relative">
                    <p
                        class="font-bold text-2xl line-clamp-1"
                        style="color: {{ $genre->text_color_1 }};"
                    >{{ $genre->name }}</p>
                    <x-hr
                        class="mt-1 rounded-full border"
                        style="border-color: {{ $genre->text_color_1 }};"
                    />

                    <div
                        class="flex justify-end gap-1.5 mt-1 w-full"
                        style="margin-left: 1.4rem;"
                    >
                        <div
                            class="rounded-full"
                            style="width: 0.875rem; height: 0.125rem; background-color: {{ $genre->text_color_1 }};"
                        ></div>

                        <div
                            class="flex"
                            style="gap: 0.125rem"
                        >
                            <div
                                class="rounded-full"
                                style="width: 0.500rem; height: 0.125rem; background-color: {{ $genre->text_color_1 }};"
                            ></div>
                            <div
                                class="rounded-full"
                                style="width: 0.125rem; height: 0.125rem; background-color: {{ $genre->text_color_1 }};"
                            ></div>
                            <div
                                class="rounded-full"
                                style="width: 0.125rem; height: 0.125rem; background-color: {{ $genre->text_color_1 }};"
                            ></div>
                        </div>
                    </div>
                </div>

                <p
                    class="mt-1 text-sm line-clamp-3"
                    style="color: {{ $genre->text_color_2 }};"
                >{{ $genre->description }}</p>
            </div>

            <div class="absolute top-0 left-0 h-full w-full rounded-lg"></div>
        </picture>
    </div>
</a>
