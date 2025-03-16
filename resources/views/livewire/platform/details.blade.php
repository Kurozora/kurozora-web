<main>
    <x-slot:title>
        {!! $platform->name !!}
    </x-slot:title>

    <x-slot:description>
        {{ $platform->synopsis }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ $platform->name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ $platform->synopsis ?? __('app.description') }}" />
        <meta property="og:image" content="{{ $platform->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/person_poster.webp') }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $platform->name }}" />
        <link rel="canonical" href="{{ route('platforms.details', $platform) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        platforms/{{ $platform->id }}
    </x-slot:appArgument>

    <div class="py-6" wire:init="loadPage">
        <section class="pt-4 pb-8 pl-4 pr-4">
            <div class="relative">
                <div class="flex flex-col flex-wrap text-center items-center">
                    <picture
                        class="relative aspect-square rounded-full overflow-hidden"
                        style="height: 128px; background-color: {{ $platform->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
                    >
                        <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $platform->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/platform_profile.webp') }}" alt="{{ $platform->name }} Profile" title="{{ $platform->name }}">

                        <div class="absolute top-0 left-0 h-full w-full border-2 border-solid border-black/20 rounded-full"></div>
                    </picture>

                    <div class="flex gap-2">
                        <p class="text-3xl font-bold">{{ $platform->name }}</p>

                        <x-nova-link :href="route('platforms.edit', $platform)">
                            @svg('pencil', 'fill-current', ['width' => '44'])
                        </x-nova-link>
                    </div>
                    @if (!empty($platform->started_at))
                        <p class="text-lg">{{ __('Released On on :x', ['x' => $platform->started_at->toFormattedDateString()]) }}</p>
                    @endif
                </div>
            </div>
        </section>

        @if ($platform->about)
            <section class="pb-8">
                <x-hr class="ml-4 mr-4 pb-5" />

                <x-section-nav>
                    <x-slot:title>
                        {{ __('About') }}
                    </x-slot:title>
                </x-section-nav>

                <x-truncated-text class="ml-4 mr-4">
                    <x-slot:text>
                        {!! nl2br(e($platform->about)) !!}
                    </x-slot:text>
                </x-truncated-text>
            </section>
        @endif

        <section class="pb-8">
                <x-hr class="ml-4 mr-4 pb-5" />

            <x-section-nav>
                <x-slot:title>
                    {{ __('Information') }}
                </x-slot:title>
            </x-section-nav>

            <div class="grid grid-cols-2 gap-4 pl-4 pr-4 sm:grid-cols-3 lg:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                <x-information-list id="released_on" title="{{ __('Released On') }}" icon="{{ asset('images/symbols/calendar.svg') }}">
                    <x-slot:information>
                        {{ $platform->started_at?->toFormattedDateString() ?? '-' }}
                    </x-slot:information>

                    <x-slot:footer>
                        @if (!empty($platform->started_at))
                            {{ __('The platform was released :x years ago.', ['x' => $platform->started_at?->age]) }}
                        @endif
                    </x-slot:footer>
                </x-information-list>

                <x-information-list id="generation" title="{{ __('Generation') }}" icon="{{ asset('images/symbols/building_2.svg') }}">
                    <x-slot:information>
                        @if (empty($platform->generation))
                            -
                        @else
                            {{ __(':x Generation', ['x' => ordinal_number($platform->generation)]) }}
                        @endif
                    </x-slot:information>
                </x-information-list>

                <x-information-list id="type" title="{{ __('Type') }}" icon="{{ asset('images/symbols/safari.svg') }}">
                    <x-slot:information>
                        {{ $platform->type->key ?? '-' }}
                    </x-slot:information>
                </x-information-list>
            </div>
        </section>

        @if ($readyToLoad)
            <livewire:components.platform.media-section :platform="$platform" :type="\App\Models\Anime::class" />

            <livewire:components.platform.media-section :platform="$platform" :type="\App\Models\Manga::class" />

            <livewire:components.platform.media-section :platform="$platform" :type="\App\Models\Game::class" />
        @else
            <x-skeletons.small-lockup />
            <x-skeletons.small-lockup />
            <x-skeletons.small-lockup />
        @endif
    </div>
</main>
