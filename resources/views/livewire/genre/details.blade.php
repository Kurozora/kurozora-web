@php
    $backgroundColor = match ($genre->color) {
        '#ffffff' => 'background: linear-gradient(-45deg, rgb(56, 62, 87) 22%, rgb(98, 112, 170) 88%)',
        default => 'background-color: ' . $genre->color
    };
@endphp

<main>
    <x-slot name="title">
        {{ $genre->name }}
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="{{ $genre->name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ $genre->description }}" />
        <meta property="og:image" content="{{ $genre->symbol_image_url ?? asset('images/static/promotional/social_preview_icon_only.png') }}" />
        <meta property="og:type" content="website" />
    </x-slot>

    <x-slot name="appArgument">
        genre/{{ $genre->id }}
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 pb-6 sm:px-6">
        <section class="mb-8 rounded-lg shadow-md overflow-hidden" style="{{ $backgroundColor }}">
            <picture class="flex justify-center">
                <img class="lazyload" width="250px" data-sizes="auto" data-src="{{ $genre->symbol_image ?? asset('images/static/icon/logo.png') }}" alt="{{ $genre->name }} Symbol" title="{{ $genre->name }}">
            </picture>

            <div class="p-3 py-5 bg-black/30 backdrop-blur text-center">
                <p class="text-white font-bold leading-tight line-clamp-1">{{ $genre->name }}</p>
                <p class="text-sm text-white/90 leading-tight">{{ $genre->description }}</p>
            </div>
        </section>

        @foreach($explorePageCategories as $key => $explorePageCategory)
            @switch($explorePageCategory->type)
                @case('most-popular-shows')
                <section class="flex pb-8 overflow-x-scroll no-scrollbar">
                    <div class="flex flex-nowrap gap-4">
                        @foreach(App\Models\Anime::mostPopular()->get() as $anime)
                            <x-lockups.banner-lockup :anime="$anime" />
                        @endforeach
                    </div>
                </section>
                @break
                @case('shows')
                <section class="pt-5 pb-8 border-t-2">
                    <x-section-nav class="flex flex-no-wrap justify-between mb-5">
                        <x-slot name="title">
                            {{ $explorePageCategory->title }}
                        </x-slot>

                        <x-slot name="action">
                            <x-simple-link href="#">{{ __('See All') }}</x-simple-link>
                        </x-slot>
                    </x-section-nav>

                    @switch($explorePageCategory->size)
                        @case('large')
                        <div class="flex mt-5 overflow-x-scroll no-scrollbar">
                            <div class="flex flex-nowrap gap-4">
                                @foreach($explorePageCategory->animes as $anime)
                                    <x-lockups.large-lockup :anime="$anime" />
                                @endforeach
                            </div>
                        </div>
                        @break
                        @case('small')
                        <div class="grid grid-flow-col-dense auto-cols-[calc(100%-2rem)] mt-5 gap-4 overflow-x-scroll no-scrollbar sm:auto-cols-[unset]">
                            @foreach($explorePageCategory->animes as $anime)
                                <x-lockups.small-lockup :anime="$anime" />
                            @endforeach
                        </div>
                        @break
                        @case('video')
                        <div class="flex mt-5 overflow-x-scroll no-scrollbar">
                            <div class="flex flex-nowrap gap-4">
                                @foreach($explorePageCategory->animes as $anime)
                                    <x-lockups.video-lockup :anime="$anime" />
                                @endforeach
                            </div>
                        </div>
                        @break
                        @default
                        {{ 'Unhandled size: ' . $explorePageCategory->size }}
                    @endswitch
                </section>
                @break
                @case('genres')
                <section class="pt-5 pb-8 border-t-2">
                    <x-section-nav class="flex flex-no-wrap justify-between mb-5">
                        <x-slot name="title">
                            {{ __('Top Genres') }}
                        </x-slot>

                        <x-slot name="action">
                            <x-simple-link href="{{ url('/genres') }}">{{ __('See All') }}</x-simple-link>
                        </x-slot>
                    </x-section-nav>

                    <div class="flex mt-5 overflow-x-scroll no-scrollbar">
                        <div class="flex flex-nowrap gap-4">
                            @foreach($explorePageCategory->genres as $genre)
                                <x-lockups.medium-lockup
                                    :href="route('genres.details', ['genre' => $genre])"
                                    :title="$genre->name"
                                    :backgroundColor="$genre->color"
                                    :backgroundImage="$genre->symbol_image ?? asset('images/static/icon/logo.png')"
                                />
                            @endforeach
                        </div>
                    </div>
                </section>
                @break
                @default
                {{ 'Unhandled type: ' . $explorePageCategory->type }}
            @endswitch
        @endforeach
    </div>
</main>
