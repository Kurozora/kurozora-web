@php use App\Enums\ParentalGuideCategory; @endphp
<main>
    <x-slot:title>
        {{ __('Parents Guide') }} | {!! $anime->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ __(':x parental guide on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $anime->title, 'y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Parents Guide') }} | {{ $anime->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __(':x parental guide on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $anime->title, 'y' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}" />
        <meta property="og:type" content="video.tv_show" />
        <meta property="video:duration" content="{{ $anime->duration }}" />
        <meta property="video:release_date" content="{{ $anime->started_at?->toIso8601String() }}" />
        <link rel="canonical" href="{{ route('anime.parentalguide', $anime) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        anime/{{ $anime->id }}/parentalguide
    </x-slot:appArgument>

    <div class="pt-4 pb-6" wire:init="loadPage">
        <section class="mb-4">
            <div>
                <div class="flex gap-1 pl-4 pr-4">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __(':x’s Parents Guide', ['x' => $anime->title]) }}</h1>
                    </div>

                    <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                    </div>
                </div>
            </div>
        </section>

        @if ($readyToLoad)
            <section class="mb-4">
                <div class="flex flex-col gap-6 pl-4 pr-4">
                    @foreach (ParentalGuideCategory::asSelectArray() as $value => $category)
                        <div class="flex flex-col gap-4 pb-6">
                            <h3 id="{{ str($category)->slug() }}" class="text-xl font-bold">
                                <a href="#{{ str($category)->slug() }}">{{ $category }}</a>
                            </h3>

                            @if ($this->parentalGuideEntries->has($value))
                                <div class="flex flex-wrap gap-4">
                                    @foreach ($this->parentalGuideEntries->get($value) as $entry)
                                        <div
                                            class="relative w-full max-w-prose bg-secondary rounded-md"
                                            x-data="{
                                                isDisabled: @js($entry->is_spoiler),
                                                hideBlur(el) {
                                                    el.classList.add('hidden')
                                                    this.isDisabled = false
                                                    this.configureHeight()
                                                },
                                                configureHeight() {
                                                    if ($refs.button.offsetHeight) {
                                                        $refs.button.style.height = $refs.content.offsetHeight + 'px'
                                                    } else {
                                                        $refs.button.style.height = null
                                                    }
                                                },
                                            }"
                                            x-init="configureHeight()"
                                            x-resize="configureHeight()"
                                        >
                                            <button
                                                class="absolute w-full pl-2 pr-2 pt-2 pb-2 backdrop-blur bg-tertiary text-sm rounded-md text-center {{ $entry->is_spoiler ? '' : 'hidden' }}"
                                                x-on:click="hideBlur($el)"
                                                x-ref="button"
                                            >
                                                @if ($entry->is_spoiler)
                                                    <p>{{ __('This reason contains spoilers — click to view') }}</p>
                                                @endif
                                            </button>

                                            <div
                                                class="pt-4 pb-4 pl-4 pr-4"
                                                x-bind:class="{'invisible' : isDisabled}"
                                                x-ref="content"
                                            >
                                                <p style="white-space: pre-wrap; overflow-wrap: break-word;">{{ $entry->reason }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="bg-secondary rounded-md p-4">
                                    <x-link href="#">{{ __('Be the first to evaluate this category.') }}</x-link>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @elseif (!$readyToLoad)
            <section>
                <div class="flex flex-wrap gap-4 justify-between pl-4 pr-4">
                    @foreach (range(1,25) as $range)
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                    @endforeach
                    <div class="w-64 md:w-80 flex-grow"></div>
                    <div class="w-64 md:w-80 flex-grow"></div>
                </div>
            </section>
        @endif
    </div>
</main>
