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

    <div class="max-w-7xl mx-auto p-6">
        <section class="flex flex-row flex-wrap gap-4 justify-between">
            <ol class="list-decimal">
                @foreach($season->episodes as $episode)
                    <li>{{ $episode->title }}</li>
{{--                <x-lockups.poster-lockup :season="$episode" />--}}
                @endforeach
            </ol>
        </section>
    </div>
</main>
