<main>
    <x-slot name="title">
        {{ $anime->title }}
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="{{ $page['title'] }}" />
        <meta property="og:image" content="{{ $page['image'] }}" />
        <meta property="og:type" content="{{ $page['type'] }}" />
    </x-slot>

    <x-slot name="appArgument">
        anime/{{ $anime->id }}
    </x-slot>

    <div class="grid grid-rows-[repeat(2,minmax(0,min-content))] lg:grid-rows-none lg:grid-cols-3 h-full mb-4 lg:mb-0">
        <div class="relative">
            <picture>
                <img class="lg:h-full lg:object-cover" src="{{ asset('images/static/star_bg_lg.jpg') }}" alt="{{ $anime->title }} Banner" title="{{ $anime->title }}">
            </picture>
        </div>

        <div class="mt-4 mx-5 lg:col-span-2 overflow-hidden">
            <section id="badges" class="flex flex-row flex-nowrap whitespace-nowrap justify-between text-center pb-5 overflow-x-scroll no-scrollbar">
                <div id="badge-1" class="flex-grow px-12">
                    <p class="inline-flex font-bold text-orange-500">
                        2.5
                        <x-star-rating star-size="sm" />
                    </p>
                    <p class="text-sm text-gray-500">187K {{ __('Ratings') }}</p>
                </div>

                <div id="badge-2" class="flex-grow px-12 border-l-2">
                    <p class="font-bold">#13</p>
                    <p class="text-sm text-gray-500">{{ __('Thriller') }}</p>
                </div>

                <div id="badge-2" class="flex-grow px-12 border-l-2">
                    <p class="font-bold">{{ $anime->tv_rating->name }}</p>
                    <p class="text-sm text-gray-500">{{ __('Rated') }}</p>
                </div>

                @if ($anime->studios()->count())
                    <div id="badge-2" class="flex-grow px-12 border-l-2">
                        <p class="font-bold">{{ $anime->studios()->first()->name }}</p>
                        <p class="text-sm text-gray-500">{{ __('Studio') }}</p>
                    </div>
                @endif
            </section>

            <section class="pt-5 pb-8 border-t-2">
                <x-section-nav class="flex flex-no-wrap justify-between mb-5">
                    <x-slot name="title">
                        {{ __('Synopsis') }}
                    </x-slot>

{{--                    <x-slot name="action">--}}
{{--                        <x-simple-button wire:click="working">{{ __('See All') }}</x-simple-button>--}}
{{--                    </x-slot>--}}
                </x-section-nav>

                <x-truncated-text>
                    <x-slot name="text">
                        {!! nl2br($anime->synopsis) !!}
                    </x-slot>
                </x-truncated-text>
            </section>

            <section class="pt-5 pb-8 border-t-2">
                <x-section-nav>
                    <x-slot name="title">
                        {{ __('Ratings') }}
                    </x-slot>
                </x-section-nav>

                <div class="flex flex-row justify-between">
                    <div class="text-center">
                        <p class="font-bold text-6xl">2.5</p>
                        <p class="font-bold text-sm text-gray-500">{{ __('out of') }} 5</p>
                    </div>

                    <div class="text-right">
                        <x-star-rating star-size="lg" />
                        <p class="text-sm text-gray-500">{{ __('Not enough ratings') }}</p>
                    </div>
                </div>
            </section>

            <section class="pt-5 pb-8 border-t-2">
                <x-section-nav>
                    <x-slot name="title">
                        {{ __('Information') }}
                    </x-slot>
                </x-section-nav>

                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-x-10 gap-y-4">
                    <x-information-list label="{{ __('Studio') }}" infromation="{{ $anime->studios()->first()->name ?? '-' }}" />
                    <x-information-list label="{{ __('Network') }}" infromation="{{ $anime->network ?? '-' }}" />
                    <x-information-list label="{{ __('Type') }}" infromation="{{ App\Enums\AnimeType::fromValue($anime->type ?? 0)->description }}" />
                    <x-information-list label="{{ __('Aired') }}" infromation="{{ $anime->first_aired ?? 'N/A' }} - {{ $anime->last_aired ?? 'N/A' }}" />
                    <x-information-list label="{{ __('Broadcast') }}" infromation="{{ $anime->broadcast }}" />
                    <x-information-list label="{{ __('Genres') }}" infromation="{{ $anime->genres ?? '-' }}" />
                    <x-information-list label="{{ __('Rating') }}" infromation="{{ $anime->tv_rating->full_name }}" />
                    <x-information-list label="{{ __('Seasons') }}" infromation="{{ $anime->seasons_count ?? '-' }}" />
                    <x-information-list label="{{ __('Episodes') }}" infromation="{{ $anime->episodes_count ?? '-' }}" />
                    <x-information-list label="{{ __('Duration') }}" infromation="{{ $anime->runtime ?? '-' }}" />
                </div>
            </section>

            <section class="pt-5 pb-2 border-t">
                <p class="text-sm text-gray-400">{{ $anime->copyright }}</p>
            </section>
        </div>
    </div>

{{--    <div class="bg-center bg-cover bg-no-repeat border-2 border-gray-100 border-opacity-25 rounded-lg shadow-lg mt-3 mb-0 mx-auto w-[180px] h-[268px]" style="background-size: 180px 268px; background-image: url('{{ $anime->poster()->url ?? asset('images/static/placeholders/anime_poster.jpg') }}')"></div>--}}

{{--    <h1 class="font-bold mt-6 mb-2">{{ $anime->title }}</h1>--}}

{{--    @if ($anime->episode_count)--}}
{{--        <h2>{{ $anime->episode_count }} {{ __('episodes') }}</h2>--}}
{{--    @endif--}}

{{--    <x-link-button href="{{ ios_app_url('anime/' . $anime->id) }}" class="rounded-full">--}}
{{--        {{ __('Open in Kurozora App') }}--}}
{{--    </x-link-button>--}}
</main>
