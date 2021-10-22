<main>
    <x-slot name="title">
        {!! $studio->name !!}
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="{{ $studio->name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ $studio->synopsis }}" />
        <meta property="og:image" content="{{ $studio->profile_image_url ?? asset('images/static/placeholders/anime_poster.webp') }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $studio->name }}" />
    </x-slot>

    <x-slot name="appArgument">
        studio/{{ $studio->id }}
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <section class="pt-5 pb-8">
            <div class="relative pb-2">
                <div class="flex flex-col flex-wrap text-center items-center">
                    <picture class="relative min-w-[128px] max-w-[128px] min-h-[128px] max-h-[128px] rounded-full overflow-hidden">
                        <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $studio->profile_image_url ?? asset('images/static/placeholders/studio_profile.webp') }}" alt="{{ $studio->name }} Profile" title="{{ $studio->name }}">

                        <div class="absolute top-0 left-0 h-full w-full border-2 border-solid border-black/20 rounded-full"></div>
                    </picture>
                    <p class="text-3xl font-bold">{{ $studio->name }}</p>
                    @if (!empty($studio->founded))
                        <p class="text-lg">{{ __('Founded on :x', ['x' => $studio->founded->toFormattedDateString()]) }}</p>
                    @endif
                </div>
            </div>
        </section>

        <section class="pt-5 pb-8 border-t-2">
            <x-section-nav>
                <x-slot name="title">
                    {{ __('Information') }}
                </x-slot>
            </x-section-nav>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-x-4 gap-y-4">
                <x-information-list id="founded" title="{{ __('Founded') }}" icon="{{ asset('images/symbols/calendar.svg') }}">
                    <x-slot name="information">
                        {{ $studio->founded?->toFormattedDateString() ?? '-' }}
                    </x-slot>

                    <x-slot name="footer">
                        @if (!empty($studio->founded))
                            {{ __('The studio was founded :x years ago.', ['x' => $studio->founded?->age]) }}
                        @endif
                    </x-slot>
                </x-information-list>

                <x-information-list id="headquarters" title="{{ __('Headquarters') }}" icon="{{ asset('images/symbols/building_2.svg') }}">
                    <x-slot name="information">
                        {{ $studio->address ?? '-' }}
                    </x-slot>
                </x-information-list>

                <x-information-list id="Website" title="{{ __('Website') }}" icon="{{ asset('images/symbols/safari.svg') }}">
                    <x-slot name="information">
                        @if (!empty($studio->website_urls))
                            <ul class="list-disc">
                                @foreach($studio->website_urls as $website_url)
                                    <li>
                                        <x-link href="{{ $website_url }}" target="_blank">
                                            {{ str_ireplace('www.', '', parse_url($website_url, PHP_URL_HOST)) ?? $website_url }}
                                        </x-link>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            -
                        @endif
                    </x-slot>
                </x-information-list>
            </div>
        </section>

        @if (!empty($studioAnime->total()))
            <section class="pt-5 pb-8 border-t-2">
                <x-section-nav>
                    <x-slot name="title">
                        {{ __('Shows') }}
                    </x-slot>

                    <x-slot name="action">
                        <x-simple-link href="{{ route('studios.anime', $studio) }}">{{ __('See All') }}</x-simple-link>
                    </x-slot>
                </x-section-nav>

                <div class="flex mt-5 overflow-x-scroll no-scrollbar">
                    <div class="flex flex-row flex-nowrap gap-4">
                        @foreach($studioAnime as $anime)
                            <x-lockups.small-lockup :anime="$anime" />
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </div>
</main>
