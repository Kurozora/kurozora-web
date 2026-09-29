<x-base-layout>
    <x-slot:title>
        {{ __('Studios') }} | {!! $parent->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('An extensive list of :x studios only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $parent->title, 'y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Studios') }} | {{ $parent->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('An extensive list of :x studios only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $parent->title, 'y' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ $parent->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/' . $ogImagePoster) }}" />
        <meta property="og:type" content="{{ $ogType }}" />
        @switch ($kind)
            @case (\App\Enums\UserLibraryKind::Anime)
                <meta property="video:duration" content="{{ $parent->duration }}" />
                <meta property="video:release_date" content="{{ $parent->started_at?->toIso8601String() }}" />
                @break
            @case (\App\Enums\UserLibraryKind::Manga)
                <meta property="book:release_date" content="{{ $parent->started_at?->toIso8601String() }}" />
                @foreach ($parent->tags() as $tag)
                    <meta property="book:tag" content="{{ $tag->name }}" />
                @endforeach
                @break
            @case (\App\Enums\UserLibraryKind::Game)
                <meta property="video:duration" content="{{ $parent->duration }}" />
                <meta property="video:release_date" content="{{ $parent->published_at?->toIso8601String() }}" />
                @break
        @endswitch
        <link rel="canonical" href="{{ $canonicalUrl }}">
    </x-slot:meta>

    <x-slot:appArgument>
        {{ $appArgumentSegment }}/{{ $parent->id }}/studios
    </x-slot:appArgument>

    <main>
        <div class="pb-6">
            <x-back-link
                :url="$parent->schemaUrl()"
                :label="$parent->title"
                :title="__(':x’s Studios', ['x' => $parent->title])"
            />

            @if ($studios->count())
                <section class="xl:safe-area-inset">
                    <x-rows.studio-lockup :studios="$studios" :is-row="false" />

                    <div class="mt-4 pl-4 pr-4">
                        {{ $studios->links() }}
                    </div>
                </section>
            @endif
        </div>
    </main>
</x-base-layout>
