<main>
    <x-slot:title>
        {{ __('Parents Guide') }} | {!! $game->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ __(':x parental guide on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $game->title, 'y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Parents Guide') }} | {{ $game->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __(':x parental guide on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $game->title, 'y' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ $game->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}" />
        <meta property="og:type" content="video.tv_show" />
        <meta property="video:duration" content="{{ $game->duration }}" />
        <meta property="video:release_date" content="{{ $game->started_at?->toIso8601String() }}" />
        <link rel="canonical" href="{{ route('games.parentalguide', $game) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        game/{{ $game->id }}/parentalguide
    </x-slot:appArgument>

    <div class="pb-6">
        <section class="sticky top-0 mb-4 pt-4 pb-4 backdrop-blur bg-blur z-10 xl:safe-area-inset">
            <div>
                <div class="flex gap-1 pl-4 pr-4">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __(':x’s Parents Guide', ['x' => $game->title]) }}</h1>
                    </div>

                    <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                        @hasrole('superAdmin')
                        <x-button wire:click="">{{ __('Vote') }}</x-button>
                        @endhasrole
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-16 xl:safe-area-inset">
            <div class="flex flex-col gap-4 pb-6 pl-4 pr-4">
                <h3 class="text-xl font-bold">{{ __('Summary') }}</h3>

                <div class="w-full max-w-prose bg-secondary rounded-md pt-4 pb-4 pl-4 pr-4">
                    <ul class="m-0 space-y-4 list-none">
                        <li>
                            <div class="flex gap-1 items-center">
                                <h4 class="font-bold">{{ __('Rating') }}:</h4>

                                <p class="text-secondary">{{ $game->tv_rating->name }} ({{ $game->tv_rating->description }})</p>
                            </div>
                        </li>

                        @foreach (App\Enums\ParentalGuideCategory::getInstances() as $category)
                            <li>
                                <a href="#{{ str($category->key)->slug() }}" class="flex gap-1 items-center">
                                    <h4 class="font-bold">{{ $category->description }}:</h4>
                                    <p class="text-secondary">{{ $game->parental_guide_stat->getAverageRating($category)->description }}</p>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </section>

        <section class="mb-4 xl:safe-area-inset">
            <div class="flex flex-col gap-6 pl-4 pr-4">
                @foreach (App\Enums\ParentalGuideCategory::getInstances() as $category)
                    <div class="flex flex-col gap-4 pb-6">
                        <div class="flex flex-col">
                            <h3 id="{{ str($category->key)->slug() }}" class="text-xl font-bold" style="scroll-margin-top: 4rem;">
                                <a href="#{{ str($category->key)->slug() }}">{{ $category->description }}</a>
                            </h3>

                            @php
                                $averageRating = $game->parental_guide_stat->getAverageRating($category);
                                [$averageRatingCount, $totalRatingCount] = $game->parental_guide_stat->getAverageRatingCount($category);
                            @endphp

                            @if($averageRatingCount !== 0 && $totalRatingCount !== 0)
                                <p class="text-secondary">{{ trans_choice('{0} :x of :y found this to have :z|[1,*] :x of :y found this :z', $averageRating->value, ['x' => $averageRatingCount, 'y' => $totalRatingCount, 'z' => strtolower($averageRating->description)]) }}</p>
                            @endif
                        </div>

                        @if ($this->parentalGuideEntries->has($category->value))
                            <div class="flex flex-wrap gap-4">
                                @foreach ($this->parentalGuideEntries->get($category->value) as $entry)
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
{{--                                <x-link href="#">{{ __('Be the first to evaluate this category.') }}</x-link>--}}
                                <x-link href="#">{{ __('It looks like we don’t have an evaluation for this category yet.') }}</x-link>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</main>
