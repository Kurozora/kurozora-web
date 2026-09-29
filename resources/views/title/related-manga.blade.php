<x-base-layout>
    <x-slot:title>
        {{ $pageTitle }} | {!! $parent->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ $pageDescription }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ $pageTitle }} | {{ $parent->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ $pageDescription }}" />
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
        {{ $appArgumentSegment }}/{{ $parent->id }}/{{ $routeSlug }}
    </x-slot:appArgument>

    <main>
        <div class="pb-6">
            <x-back-link
                :url="$parent->schemaUrl()"
                :label="$parent->title"
                :title="$heading"
            />

            <section class="xl:safe-area-inset">
                <x-rows.small-lockup :related-mangas="$relations" :is-row="false" />

                <div class="mt-4 pl-4 pr-4">
                    {{ $relations->links() }}
                </div>
            </section>
        </div>
    </main>
</x-base-layout>
