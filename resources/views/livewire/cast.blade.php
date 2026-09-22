<main>
    <x-slot:title>
        {{ __('Cast') }} | {!! $this->parent->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover all cast of :x only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $this->parent->title, 'y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Cast') }} | {{ $this->parent->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover all cast of :x on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $this->parent->title, 'y' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ $this->parent->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/' . $this->ogImagePoster) }}" />
        <meta property="og:type" content="{{ $this->ogType }}" />
        @switch ($kind)
            @case (\App\Enums\UserLibraryKind::Anime)
                <meta property="video:duration" content="{{ $this->parent->duration }}" />
                <meta property="video:release_date" content="{{ $this->parent->started_at?->toIso8601String() }}" />
                @break
            @case (\App\Enums\UserLibraryKind::Manga)
                <meta property="book:release_date" content="{{ $this->parent->started_at?->toIso8601String() }}" />
                @foreach ($this->parent->tags() as $tag)
                    <meta property="book:tag" content="{{ $tag->name }}" />
                @endforeach
                @break
            @case (\App\Enums\UserLibraryKind::Game)
                <meta property="video:duration" content="{{ $this->parent->duration }}" />
                <meta property="video:release_date" content="{{ $this->parent->started_at?->toIso8601String() }}" />
                @break
        @endswitch
        <link rel="canonical" href="{{ $this->canonicalUrl }}">
    </x-slot:meta>

    <x-slot:appArgument>
        {{ $this->appArgumentSegment }}/{{ $this->parent->id }}/cast
    </x-slot:appArgument>

    <div class="pb-6" wire:init="loadPage">
        <x-back-link
            :url="$this->parent->schemaUrl()"
            :label="$this->parent->title"
            :title="__(':x’s Cast', ['x' => $this->parent->title])"
        />

        @if ($this->cast->count())
            <section class="xl:safe-area-inset">
                @switch ($kind)
                    @case (\App\Enums\UserLibraryKind::Manga)
                        <x-rows.character-lockup :manga-casts="$this->cast" :is-row="false" />
                        @break
                    @default
                        <div class="flex flex-wrap gap-4 justify-start pl-4 pr-4">
                            @foreach ($this->cast as $castEntry)
                                <x-lockups.cast-lockup :cast="$castEntry" :isRow="false" />
                            @endforeach

                            <div class="w-[98%] sm:w-96 flex-grow"></div>
                            <div class="w-[98%] sm:w-96 flex-grow"></div>
                            <div class="w-[98%] sm:w-96 flex-grow"></div>
                            <div class="w-[98%] sm:w-96 flex-grow"></div>
                        </div>
                @endswitch

                <div class="mt-4 pl-4 pr-4">
                    {{ $this->cast->links() }}
                </div>
            </section>
        @elseif (!$readyToLoad)
            <section class="xl:safe-area-inset">
                <x-skeletons.lockup-row lockup="cast" :is-row="false" />
            </section>
        @endif
    </div>
</main>
