<main>
    <x-slot name="title">
        {{ __('Cast') }} | {!! $anime->title !!}
    </x-slot>

    <x-slot name="description">
        {{ __('Discover all cast of :x only on Kurozora, the largest, free online anime and manga database in the world.', ['x' => $anime->title]) }}
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="{{ __('Cast') }} | {{ $anime->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover all cast of :x on Kurozora, the largest, free online anime and manga database in the world.', ['x' => $anime->title]) }}" />
        <meta property="og:image" content="{{ $anime->poster_image_url ?? asset('images/static/placeholders/anime_poster.webp') }}" />
        <meta property="og:type" content="video.tv_show" />
        <meta property="video:duration" content="{{ $anime->duration }}" />
        <meta property="video:release_date" content="{{ $anime->first_aired }}" />
        <link rel="canonical" href="{{ route('anime.cast', $anime) }}">
    </x-slot>

    <x-slot name="appArgument">
        anime/{{ $anime->id }}/cast
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <section class="grid gap-4 sm:grid-cols-2 sm:auto-cols-[unset] lg:grid-cols-3">
            @foreach($cast as $animeCast)
                <x-lockups.cast-lockup :cast="$animeCast" :isRow="false" />
            @endforeach
        </section>

        <section class="mt-4">
            {{ $cast->links() }}
        </section>
    </div>
</main>
