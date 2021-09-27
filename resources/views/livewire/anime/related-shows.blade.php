<main>
    <x-slot name="title">
        {{ __('Relations') }} | {!! $anime->title !!}
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="{{ __('Relations') }} | {{ $anime->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ $anime->synopsis }}" />
        <meta property="og:image" content="{{ $anime->poster_image_url ?? asset('images/static/placeholders/anime_poster.jpg') }}" />
        <meta property="og:type" content="video.tv_show" />
        <meta property="video:duration" content="{{ $anime->duration }}" />
        <meta property="video:release_date" content="{{ $anime->first_aired }}" />
    </x-slot>

    <x-slot name="appArgument">
        anime/{{ $anime->id }}/seasons
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 pb-6 sm:px-6">
        <section class="flex flex-row flex-wrap gap-4 justify-between">
            @foreach($animeRelations as $animeRelation)
                <x-lockups.small-lockup :anime="$animeRelation->related_anime" :relation="$animeRelation->relation" />
            @endforeach
        </section>

        {{ $animeRelations->links() }}
    </div>
</main>
