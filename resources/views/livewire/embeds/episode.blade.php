<main>
    <x-slot:title>
        {!! __('Watch :x episode :y', ['x' => $this->anime?->title, 'y' => $episode->number_total]) !!} | {!! $episode->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Watch English subbed anime episodes for free.') }}
        {{ $episode->synopsis }}
    </x-slot:description>

    <x-slot:meta>
        <meta name="robots" content="noindex">

        <meta property="og:title" content="{{ __(':x episode :y', ['x' => $this->anime?->title, 'y' => $episode->number_total]) }} | {{ $episode->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ $episode->synopsis ?? __('app.description') }}" />
        <meta property="og:image" content="{{ $episode->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? asset('images/static/placeholders/episode_banner.webp') }}" />
        <meta property="og:type" content="video.episode" />
        <meta property="og:video:type" content="text/html">
        <meta property="video:series" content="{{ $this->anime?->title }}" />
        <meta property="og:video:url" content="{{ route('embed.episodes', $episode) }}">
        <meta property="og:video:height" content="1080">
        <meta property="og:video:width" content="1920">
        <meta property="video:duration" content="{{ $episode->duration }}" />
        <meta property="video:release_date" content="{{ $episode->started_at?->toIso8601String() }}" />
        <meta property="twitter:title" content="{{ $episode->title }} — {{ config('app.name') }}" />
        <meta property="twitter:description" content="{{ $episode->synopsis }}" />
        <meta property="twitter:card" content="summary_large_image" />
        <meta property="twitter:image" content="{{ $episode->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="twitter:image:alt" content="{{ $episode->synopsis }}" />
        <link rel="canonical" href="{{ route('embed.episodes', $episode) }}">
        <x-misc.schema>
            "@type":"TVEpisode",
            "url":"/episode/{{ $episode->id }}/",
            "name": "{{ $episode->title }}",
            "alternateName": "{{ $this->anime?->original_title }}",
            "image": "{{ $episode->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? asset('images/static/promotional/social_preview_icon_only.webp') }}",
            "description": "{{ $episode->synopsis }}",
            "aggregateRating": {
                "@type":"AggregateRating",
                "itemReviewed": {
                    "@type": "TVEpisode",
                    "image": [
                        "{{ $episode->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? asset('images/static/promotional/social_preview_icon_only.webp') }}"
                    ],
                    "name": "{{ $episode->title }}"
                },
                "ratingCount": {{ $episode->mediaStat->rating_count ?? 1 }},
                "bestRating": 5,
                "worstRating": 0,
                "ratingValue": {{ $episode->mediaStat->rating_average ?? 2.5 }}
            },
            "contentRating": "{{ $this->episode->tv_rating->name }}",
            "genre": {!! $this->anime?->genres->pluck('name') !!},
            "datePublished": "{{ $episode->started_at?->format('Y-m-d') }}",
            "keywords": "anime,episode{{ (',' . $this->anime?->keywords) ?? '' }}",
            "creator":[
                {
                    "@type":"Organization",
                    "url":"/studio/{{ $this->anime?->studios?->firstWhere('is_studio', '=', true)?->id ?? $this->anime?->studios->first()?->id }}/"
                }
            ]
            @if (!empty($this->video))
                ,"trailer": {
                    "@type":"VideoObject",
                    "name":"{{ $episode->title }}",
                    "description":"Official Trailer",
                    "embedUrl": "{{ $this->video->first()->getUrl() }}",
                    "thumbnailUrl": "{{ $episode->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? asset('images/static/promotional/social_preview_icon_only.webp') }}",
                    "uploadDate": "{{ $episode->started_at?->format('Y-m-d') }}"
                }
            @endif
        </x-misc.schema>
    </x-slot:meta>

    <x-slot:styles>
        <link rel="preload" href="{{ url(mix('css/watch.css')) }}" as="style">
        <link rel="stylesheet" href="{{ url(mix('css/watch.css')) }}">
        <link rel="preload" href="{{ url(mix('css/chat.css')) }}" as="style">
        <link rel="stylesheet" href="{{ url(mix('css/chat.css')) }}">
    </x-slot:styles>

    <x-slot:appArgument>
        episodes/{{ $episode->id }}
    </x-slot:appArgument>

    <x-slot:scripts>
        <script src="{{ url(mix('js/watch.js')) }}"></script>
    </x-slot:scripts>

    <div style="height: 100vh; max-height: calc(100vh)">
        <div class="relative w-full h-full overflow-hidden z-0" style="background-color: {{ $episode->getFirstMedia(\App\Enums\MediaCollection::Banner)?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};">
            <div class="relative w-full h-full overflow-hidden z-10">
                @if (!empty($this->video))
                    {!! $this->video->getEmbed(['currentTime' => $t]) !!}
                @else
                    <x-picture
                        class="h-full"
                        style="background-color: {{ $episode->getFirstMedia(\App\Enums\MediaCollection::Banner)?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
                    >
                        <img class="w-full h-full aspect-video object-cover lazyload" data-sizes="auto" data-src="{{ $episode->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? asset('images/static/placeholders/anime_banner.webp') }}" alt="{{ $episode->title }} Banner" title="{{ $episode->title }}">
                    </x-picture>
                @endif
            </div>
        </div>
    </div>
</main>
