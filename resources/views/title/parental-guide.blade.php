@php
    $signInAction = 'Livewire.navigate(' . Js::from(route('sign-in')) . ')';
    $addAction = auth()->check() ? "Livewire.dispatch('parental-guide-box-open', {})" : $signInAction;
@endphp

<x-base-layout>
    <x-slot:title>
        {{ __('Parents Guide') }} | {!! $parent->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ __(':x parental guide on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $parent->title, 'y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Parents Guide') }} | {{ $parent->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __(':x parental guide on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $parent->title, 'y' => config('app.name')]) }}" />
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
        {{ $appArgumentSegment }}/{{ $parent->id }}/parentalguide
    </x-slot:appArgument>

    <main>
        <div class="pb-6">
            <x-back-link
                :url="$parent->schemaUrl()"
                :label="$parent->title"
                :title="__(':x’s Parents Guide', ['x' => $parent->title])"
            >
                <x-slot:actions>
                    <x-button x-data x-on:click="{{ $addAction }}">{{ __('Add') }}</x-button>
                </x-slot:actions>
            </x-back-link>

            <div data-paginated="parental-guide" data-paginated-refresh-on="parental-guide-updated">
                <section class="mb-16 xl:safe-area-inset">
                    <div class="flex flex-col gap-4 pb-6 pl-4 pr-4">
                        <h3 class="text-xl font-bold">{{ __('Summary') }}</h3>

                        <div class="w-full max-w-prose bg-secondary rounded-md pt-4 pb-4 pl-4 pr-4">
                            <ul class="m-0 space-y-4 list-none">
                                <li>
                                    <div class="flex gap-1 items-center">
                                        <h4 class="font-bold">{{ __('Rating') }}:</h4>

                                        <p class="text-secondary">{{ $parent->tvRating->name }} ({{ $parent->tvRating->description }})</p>
                                    </div>
                                </li>

                                @foreach (App\Enums\ParentalGuideCategory::getInstances() as $category)
                                    <li>
                                        <a href="#{{ $category->urlSlug() }}" class="flex gap-1 items-center">
                                            <h4 class="font-bold">{{ $category->description }}:</h4>
                                            <p class="text-secondary">{{ $parent->parentalGuideStat->getAverageRating($category)->description }}</p>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </section>

                <section class="mb-4 xl:safe-area-inset">
                    <div class="flex flex-col gap-6">
                        @foreach (App\Enums\ParentalGuideCategory::getInstances() as $category)
                            @php
                                $categorySlug = $category->urlSlug();
                                $averageRating = $parent->parentalGuideStat->getAverageRating($category);
                                [$averageRatingCount, $totalRatingCount] = $parent->parentalGuideStat->getAverageRatingCount($category);
                                $emptyCategoryAction = auth()->check()
                                    ? "Livewire.dispatch('parental-guide-box-open', { category: " . $category->value . ' })'
                                    : $signInAction;
                            @endphp

                            <div id="{{ $categorySlug }}" class="flex flex-col gap-4 pb-6" style="scroll-margin-top: 4rem;">
                                <x-section-nav class="!mb-0">
                                    <x-slot:title>
                                        <a href="#{{ $categorySlug }}">{{ $category->description }}</a>
                                    </x-slot:title>

                                    @if($averageRatingCount !== 0 && $totalRatingCount !== 0)
                                        <x-slot:description>
                                            {{ trans_choice('{0} :x of :y found this to have :z|[1,*] :x of :y found this :z', $averageRating->value, ['x' => $averageRatingCount, 'y' => $totalRatingCount, 'z' => strtolower($averageRating->description)]) }}
                                        </x-slot:description>
                                    @endif

                                    <x-slot:action>
                                        <x-section-nav-link href="{{ $categoryUrls[$category->value] }}">{{ __('See All') }}</x-section-nav-link>
                                    </x-slot:action>
                                </x-section-nav>

                                @if ($entries->has($category->value))
                                    <div class="flex flex-wrap gap-4 pl-4 pr-4">
                                        @foreach ($entries->get($category->value)->take(5) as $entry)
                                            <x-lockups.parental-guide-entry-lockup :entry="$entry" />
                                        @endforeach
                                    </div>
                                @else
                                    <button
                                        type="button"
                                        class="bg-secondary rounded-md p-4 ml-4 mr-4 text-left w-full max-w-prose"
                                        x-data
                                        x-on:click="{{ $emptyCategoryAction }}"
                                    >
                                        <span class="text-tint underline">{{ __('It looks like we don’t have an evaluation for this category yet.') }}</span>
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>
        </div>
    </main>

    <x-parental-guide.modals />

    @auth
        <livewire:components.parental-guide-box :model-id="$parent->id" :model-type="$parent->getMorphClass()" />
    @endauth
</x-base-layout>
