<x-base-layout>
    <x-slot:title>
        {{ __('Staff') }} | {!! $parent->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover all staff of :x only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $parent->title, 'y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Staff') }} | {{ $parent->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover all staff of :x on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $parent->title, 'y' => config('app.name')]) }}" />
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
                <meta property="video:release_date" content="{{ $parent->started_at?->toIso8601String() }}" />
                @break
        @endswitch
        <link rel="canonical" href="{{ $canonicalUrl }}">
    </x-slot:meta>

    <x-slot:appArgument>
        {{ $appArgumentSegment }}/{{ $parent->id }}/staff
    </x-slot:appArgument>

    <main>
        <div class="pb-6">
            <x-back-link
                :url="$parent->schemaUrl()"
                :label="$parent->title"
                :title="__(':x’s Staff', ['x' => $parent->title])"
            />

            @if ($mediaStaff->count())
                <section class="xl:safe-area-inset" data-paginated="staff">
                    <x-rows.person-lockup :media-staff="$mediaStaff" :is-row="false" />

                    <div class="mt-4 pl-4 pr-4">
                        {{ $mediaStaff->links() }}
                    </div>
                </section>
            @endif
        </div>
    </main>
</x-base-layout>
