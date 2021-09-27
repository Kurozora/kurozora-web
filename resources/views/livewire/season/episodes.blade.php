<main>
    <x-slot name="title">
        {{ __('Episodes') }} | {!! $season->title !!}
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="Episodes | {{ $season->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ $season->synopsis }}" />
        <meta property="og:image" content="{{ $season->poster_image_url ?? asset('images/static/placeholders/anime_poster.jpg') }}" />
        <meta property="og:type" content="video.tv_show" />
        <meta property="video:duration" content="{{ $season->duration }}" />
        <meta property="video:release_date" content="{{ $season->first_aired }}" />
    </x-slot>

    <x-slot name="appArgument">
        seasons/{{ $season->id }}/episodes
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <section class="grid sm:grid-cols-2 sm:auto-cols-[unset] lg:grid-cols-3 gap-4">
            @foreach($episodes as $episode)
                <x-lockups.episode-lockup :episode="$episode" />
            @endforeach
        </section>

        <section class="mt-4">
            {{ $episodes->links() }}
        </section>
    </div>
</main>
