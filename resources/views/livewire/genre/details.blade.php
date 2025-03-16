@php
    $backgroundColor = match ($genre->color) {
        '#ffffff' => 'background: linear-gradient(-45deg, rgb(56, 62, 87) 22%, rgb(98, 112, 170) 88%)',
        default => 'background-color: ' . $genre->color
    };
@endphp

<main>
    <x-slot:title>
        {{ $genre->name }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover the extensive list of :x anime only on Kurozora, the largest, free online anime, manga, game & music database in the world.', ['x' => $genre->name]) }} {{ $genre->description }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ $genre->name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover the extensive list of :x anime only on Kurozora, the largest, free online anime, manga, game & music database in the world.', ['x' => $genre->name]) }} {{ $genre->description }}" />
        <meta property="og:image" content="{{ $genre->getFirstMediaFullUrl(\App\Enums\MediaCollection::Symbol()) ?? asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('genres.details', $genre) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        genres/{{ $genre->id }}
    </x-slot:appArgument>

    <div class="py-6">
        <section class="relative mb-8 ml-4 mr-4 rounded-lg shadow-md overflow-hidden" style="{{ $backgroundColor }}">
            <picture class="flex justify-center">
                <img class="aspect-square lazyload" width="250px" data-sizes="auto" data-src="{{ $genre->getFirstMediaFullUrl(\App\Enums\MediaCollection::Symbol()) ?? asset('images/static/icon/logo.webp') }}" alt="{{ $genre->name }} Symbol" title="{{ $genre->name }}">
            </picture>

            <div class="pr-3 pl-3 pt-4 pb-4 bg-black/30 backdrop-blur text-center">
                <p class="text-white font-bold leading-tight line-clamp-1">{{ $genre->name }}</p>
                <p class="text-sm text-white/90 leading-tight">{{ $genre->description }}</p>
            </div>
        </section>

        <section wire:init="loadPage">
            @foreach ($this->exploreCategories as $index => $exploreCategory)
                @switch($exploreCategory->type)
                @case(\App\Enums\ExploreCategoryTypes::MostPopularShows)
                    <section class="pb-8">
                        <div class="flex flex-nowrap gap-4 mt-5 snap-mandatory snap-x overflow-x-scroll no-scrollbar">
                            @foreach ($exploreCategory->mostPopular(\App\Models\Anime::class, $genre)->exploreCategoryItems as $categoryItem)
                                <x-lockups.banner-lockup :anime="$categoryItem->model" />
                            @endforeach
                        </div>
                    </section>
                    @break
                @default
                    <livewire:components.explore-category-section :index="$index" :exploreCategory="$exploreCategory" :genre="$genre" />
                @endswitch
            @endforeach
        </section>

        @if (!$readyToLoad)
            <section  class="pt-4 pb-8 border-t border-primary">
                <div style="height: 314px">
                    <div class="flex gap-2 justify-between mb-5 pl-4 pr-4">
                        <div>
                            <p class="bg-secondary" style="width: 168px; height: 28px"></p>
                            <p class="bg-secondary" style="width: 228px; height: 22px"></p>
                        </div>

                        <div class="flex flex-wrap gap-2 justify-end"></div>
                    </div>

                    <div class="flex gap-4 justify-between pl-4 pr-4 snap-mandatory snap-x overflow-x-scroll no-scrollbar">
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                    </div>
                </div>
            </section>
            <section  class="pt-4 pb-8 border-t border-primary">
                <div style="height: 314px">
                    <div class="flex gap-2 justify-between mb-5 pl-4 pr-4">
                        <div>
                            <p class="bg-secondary" style="width: 168px; height: 28px"></p>
                            <p class="bg-secondary" style="width: 228px; height: 22px"></p>
                        </div>

                        <div class="flex flex-wrap gap-2 justify-end"></div>
                    </div>

                    <div class="flex gap-4 justify-between pl-4 pr-4 snap-mandatory snap-x overflow-x-scroll no-scrollbar">
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                    </div>
                </div>
            </section>
            <section  class="pt-4 pb-8 border-t border-primary">
                <div style="height: 314px">
                    <div class="flex gap-2 justify-between mb-5 pl-4 pr-4">
                        <div>
                            <p class="bg-secondary" style="width: 168px; height: 28px"></p>
                            <p class="bg-secondary rounded-md" style="width: 228px; height: 22px"></p>
                        </div>

                        <div class="flex flex-wrap gap-2 justify-end"></div>
                    </div>

                    <div class="flex gap-4 justify-between pl-4 pr-4 snap-mandatory snap-x overflow-x-scroll no-scrollbar">
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                    </div>
                </div>
            </section>
            <section  class="pt-4 pb-8 border-t border-primary">
                <div style="height: 314px">
                    <div class="flex gap-2 justify-between mb-5 pl-4 pr-4">
                        <div>
                            <p class="bg-secondary rounded-md" style="width: 168px; height: 28px"></p>
                            <p class="bg-secondary rounded-md" style="width: 228px; height: 22px"></p>
                        </div>

                        <div class="flex flex-wrap gap-2 justify-end"></div>
                    </div>

                    <div class="flex gap-4 justify-between pl-4 pr-4 snap-mandatory snap-x overflow-x-scroll no-scrollbar">
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                    </div>
                </div>
            </section>
        @endif
    </div>
</main>
