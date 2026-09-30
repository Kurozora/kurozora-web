<x-base-layout>
    <x-slot:title>
        {{ __('Re:CAP :x', ['x' => $period->year]) }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Look back at the top anime, manga, games and songs that defined your year. Discover your personalized :x :y Re:CAP.', ['x' => $period->year, 'y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Re:CAP :x', ['x' => $period->year]) }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Look back at the top anime, manga, games and songs that defined your year. Discover your personalized :x :y Re:CAP.', ['x' => $period->year, 'y' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ asset('images/static/banners/kurozora_recap_year.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('recap.index') }}">
    </x-slot:meta>

    <x-slot:appArgument>
        recap?year={{ $period->year }}&month={{ $period->month }}
    </x-slot:appArgument>

    <x-slot:scripts>
        @vite(['resources/js/recap-share.js'])
    </x-slot:scripts>

    <main class="relative">
        <div data-paginated="recap">
            @if ($period->recapYears()->count())
                @php($shareCards = $period->shareCards())

                <header
                    id="header"
                    class="sticky top-0 z-10 xl:safe-area-inset"
                >
                    <div class="flex flex-row items-center justify-between gap-4 pt-6 pl-4 pr-4">
                        <h1 class="text-primary text-3xl font-bold">{{ __('Re:CAP') }}</h1>

                        <div class="flex items-center gap-2">
                            @if (isset($shareCards['cards']['summary']))
                                <x-recap-share-button class="w-9 h-9" share-key="summary" :label="__('Share your Re:CAP')" />
                            @endif

                            <form id="recap-form" method="get" action="{{ route('recap.index') }}" data-search-bar>
                                <input type="hidden" name="month" value="{{ request()->filled('month') ? $period->month : '' }}">

                                <x-select-button
                                    rounded="full"
                                    chevronClass="w-5 h-5 text-primary"
                                    class="pl-4 pr-8 pt-1 pb-1 bg-blur backdrop-blur text-primary text-lg font-semibold border-0 shadow-md focus:ring-0"
                                    aria-label="{{ __('Select a year to see your recap') }}"
                                    name="year"
                                    x-data
                                    x-on:change="$el.form.elements.month.value = ''"
                                >
                                    @foreach ($period->recapYears() as $recap)
                                        <option value="{{ $recap->year === now()->year ? '' : $recap->year }}" @selected($recap->year === $period->year)>{{ $recap->year }}</option>
                                    @endforeach
                                </x-select-button>
                            </form>
                        </div>
                    </div>
                </header>

                <div class="mx-auto xl:safe-area-inset">
                    <div class="flex items-center isolate">
                        @if ($period->hasYearlyRecap())
                            <div class="relative flex-none pl-4 z-10" key="select-{{ $period->year }}-0">
                                @if ($period->month === 0)
                                    <x-tinted-pill-button>
                                        <p class="pr-2 pl-2 text-base">{{ $period->year }}</p>
                                    </x-tinted-pill-button>
                                @else
                                    <x-tinted-pill-button
                                        class="shadow-none text-secondary hover:text-primary"
                                        color="transparent"
                                        type="button"
                                        form="recap-form"
                                        data-search-choice="month"
                                        value="0"
                                    >
                                        <p class="pr-2 pl-2 text-base">{{ $period->year }}</p>
                                    </x-tinted-pill-button>
                                @endif
                            </div>
                        @endif

                        <div
                            @class([
                                'flex flex-1 gap-6 min-w-0 pt-4 pb-4 pr-4 whitespace-nowrap overflow-x-scroll no-scrollbar md:gap-20',
                                '-ml-3 pl-3' => $period->hasYearlyRecap(),
                                'pl-4' => !$period->hasYearlyRecap(),
                            ])
                            style="mask: linear-gradient(90deg, transparent, #000 {{ $period->hasYearlyRecap() ? '0.75rem' : '2%' }}, #000 98%, transparent 100%);"
                        >
{{--                @if ($this->year !== now()->year || now()->month === 12)--}}
{{--                    <span wire:key="select-{{ $this->year }}">--}}
{{--                        <template x-if="month === null">--}}
{{--                            <x-tinted-pill-button>--}}
{{--                                <p class="pr-2 pl-2 text-base">{{ $this->year }}</p>--}}
{{--                            </x-tinted-pill-button>--}}
{{--                        </template>--}}

{{--                        <template x-if="month !== null">--}}
{{--                            <x-tinted-pill-button--}}
{{--                                color="transparent"--}}
{{--                                x-on:click="month = null"--}}
{{--                            >--}}
{{--                                <p class="pr-2 pl-2 text-base text-white">{{ $this->year }}</p>--}}
{{--                            </x-tinted-pill-button>--}}
{{--                        </template>--}}
{{--                    </span>--}}
{{--                @endif--}}

                            @if ($period->hasYearlyRecap())
                                <div class="flex-none" aria-hidden="true"></div>
                            @endif

                            @foreach ($period->recapMonths() as $recap)
                                <div key="select-{{ $recap->year }}-{{ $recap->month }}">
                                    @if ($period->month === $recap->month)
                                        <x-tinted-pill-button>
                                            <p class="pr-2 pl-2 text-base">{{ $recap->period_title }}</p>
                                        </x-tinted-pill-button>
                                    @else
                                        <x-tinted-pill-button
                                            class="shadow-none text-secondary hover:text-primary"
                                            color="transparent"
                                            type="button"
                                            form="recap-form"
                                            data-search-choice="month"
                                            value="{{ $recap->month }}"
                                        >
                                            <p class="pr-2 pl-2 text-base">{{ $recap->period_title }}</p>
                                        </x-tinted-pill-button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <div class="mx-auto pb-20 text-primary" data-loading="hide">
                <x-recap-backdrop :recap="$period->backdropRecap()" />

                @if ($period->recaps()->count())
                    <section class="mt-8">
                        <div hidden data-recap-share-cards="{{ json_encode($period->shareCards()) }}"></div>
                        <div class="xl:safe-area-inset">
                            <h2 class="pt-10 pl-4 pr-4 pb-10 text-secondary text-2xl font-semibold">{{ $period->periodHeadingParts()[0] }}<x-rolling-text class="text-primary" :text="$period->periodName()" />{{ $period->periodHeadingParts()[1] }}</h2>
                        </div>

                        @foreach ($period->recaps() as $recap)
                            @if ($recap->recapItems->count())
                                @switch($recap->type)
                                    @case(\App\Models\Anime::class)
                                        <section class="mt-10">
                                            <div class="xl:safe-area-inset">
                                                <div class="pl-4 pr-4">
                                                    <h2 class="font-semibold text-xl">{{ $period->sectionTitles()[\App\Models\Anime::class] }}</h2>
                                                    <p class="text-secondary font-semibold">{{ __(':x total series', ['x' => $recap->total_series_count]) }}</p>
                                                </div>
                                            </div>

                                            <x-rows.small-lockup-columns class="mt-4" :models="$recap->recapItems->pluck('model')" :details="$period->recapItemDetails()[$recap->id] ?? []" :favoritedIDs="$period->favoritedModelIDs()[\App\Models\Anime::class] ?? []" />
                                        </section>
                                        @break
                                    @case(\App\Models\Manga::class)
                                        <section class="mt-10">
                                            <div class="xl:safe-area-inset">
                                                <div class="pl-4 pr-4">
                                                    <h2 class="font-semibold text-xl">{{ $period->sectionTitles()[\App\Models\Manga::class] }}</h2>
                                                    <p class="text-secondary font-semibold">{{ __(':x total series', ['x' => $recap->total_series_count]) }}</p>
                                                </div>
                                            </div>

                                            <x-rows.small-lockup-columns class="mt-4" :models="$recap->recapItems->pluck('model')" :details="$period->recapItemDetails()[$recap->id] ?? []" :favoritedIDs="$period->favoritedModelIDs()[\App\Models\Manga::class] ?? []" />
                                        </section>
                                        @break
                                    @case(\App\Models\Game::class)
                                        <section class="mt-10">
                                            <div class="xl:safe-area-inset">
                                                <div class="pl-4 pr-4">
                                                    <h2 class="font-semibold text-xl">{{ $period->sectionTitles()[\App\Models\Game::class] }}</h2>
                                                    <p class="text-secondary font-semibold">{{ __(':x total series', ['x' => $recap->total_series_count]) }}</p>
                                                </div>
                                            </div>

                                            <x-rows.small-lockup-columns class="mt-4" :models="$recap->recapItems->pluck('model')" :details="$period->recapItemDetails()[$recap->id] ?? []" :favoritedIDs="$period->favoritedModelIDs()[\App\Models\Game::class] ?? []" />
                                        </section>
                                        @break
                                    @case(\App\Models\Genre::class)
                                        <section class="mt-10 xl:safe-area-inset">
                                            <div class="pl-4 pr-4">
                                                <h2 class="font-semibold text-xl">{{ $period->sectionTitles()[\App\Models\Genre::class] }}</h2>

                                                <x-lockups.recap-genre-lockup class="mt-4" :recap="$recap" share-key="genres" />
                                            </div>
                                        </section>
                                        @break
                                    @case(\App\Models\Theme::class)
                                        <section class="mt-10 xl:safe-area-inset">
                                            <div class="pl-4 pr-4">
                                                <h2 class="font-semibold text-xl">{{ $period->sectionTitles()[\App\Models\Theme::class] }}</h2>

                                                <x-lockups.recap-genre-lockup class="mt-4" :recap="$recap" share-key="themes" />
                                            </div>
                                        </section>
                                        @break
                                    @case(\App\Models\Studio::class)
                                        <section class="mt-10">
                                            <div class="xl:safe-area-inset">
                                                <h2 class="pl-4 pr-4 font-semibold text-xl">{{ $period->sectionTitles()[\App\Models\Studio::class] }}</h2>
                                            </div>

                                            <x-rows.container class="mt-4">
                                                @foreach ($recap->recapItems->whereNotNull('model') as $recapItem)
                                                    <x-lockups.studio-lockup :studio="$recapItem->model" :rank="$loop->iteration" :detail="$period->recapItemDetails()[$recap->id][$recapItem->model_id] ?? null" :is-ranked="true" />
                                                @endforeach
                                            </x-rows.container>
                                        </section>
                                        @break
                                    @case(\App\Models\Character::class)
                                        <section class="mt-10">
                                            <div class="xl:safe-area-inset">
                                                <h2 class="pl-4 pr-4 font-semibold text-xl">{{ $period->sectionTitles()[\App\Models\Character::class] }}</h2>
                                            </div>

                                            <x-rows.container class="mt-4" lockup="person-wide">
                                                @foreach ($recap->recapItems->whereNotNull('model') as $recapItem)
                                                    <x-lockups.character-lockup :character="$recapItem->model" :rank="$loop->iteration" :is-ranked="true" :is-wide="true" />
                                                @endforeach
                                            </x-rows.container>
                                        </section>
                                        @break
                                    @case(\App\Models\Person::class)
                                    @case(\App\Models\MediaStaff::class)
                                        <section class="mt-10">
                                            <div class="xl:safe-area-inset">
                                                <h2 class="pl-4 pr-4 font-semibold text-xl">{{ $period->sectionTitles()[$recap->type] }}</h2>
                                            </div>

                                            <x-rows.container class="mt-4" lockup="person-wide">
                                                @foreach ($recap->recapItems->whereNotNull('model') as $recapItem)
                                                    <x-lockups.person-lockup :person="$recapItem->model" :staff-role="$period->recapItemDetails()[$recap->id][$recapItem->model_id] ?? null" :rank="$loop->iteration" :is-ranked="true" :is-wide="true" />
                                                @endforeach
                                            </x-rows.container>
                                        </section>
                                        @break
                                    @default
                                        @if (app()->isLocal())
                                            {{ 'Unhandled type: ' . $recap->type }}
                                        @endif
                                @endswitch
                            @endif
                        @endforeach
                    </section>

                    @foreach ($period->topTitlesByMonth() as $topTitles)
                        <section class="mt-10">
                            <div class="xl:safe-area-inset">
                                <h2 class="pl-4 pr-4 font-semibold text-xl">{{ $topTitles['title'] }}</h2>
                            </div>

                            <x-rows.small-lockup-columns class="mt-4" :models="$topTitles['models']" :eyebrows="$topTitles['eyebrows']" :is-ranked="false" />
                        </section>
                    @endforeach

                    @foreach (['habits' => __('Your Habits'), 'activity' => __('Your Activity')] as $section => $sectionTitle)
                        @if (!empty($period->recapStatCards()[$section]))
                            <section class="mt-10">
                                <div class="xl:safe-area-inset">
                                    <h2 class="pl-4 pr-4 font-semibold text-xl">{{ $sectionTitle }}</h2>
                                </div>

                                <div class="flex flex-nowrap gap-4 mt-2 pt-2 pl-4 pr-4 pb-4 snap-mandatory snap-x scroll-pl-4 overflow-x-scroll no-scrollbar xl:safe-area-inset-scroll">
                                    @foreach ($period->recapStatCards()[$section] as $statCard)
                                        <x-lockups.recap-stat-lockup :title="$statCard['title']" :value="$statCard['value']" :caption="$statCard['caption']" />
                                    @endforeach
                                </div>
                            </section>
                        @endif
                    @endforeach

                    @if ($period->recapComparisons()->isNotEmpty())
                        <section class="mt-10">
                            <div class="xl:safe-area-inset">
                                <h2 class="pl-4 pr-4 font-semibold text-xl">{{ __('Remember Last Year?') }}</h2>
                            </div>

                            <div class="flex flex-nowrap gap-4 mt-2 pt-2 pl-4 pr-4 pb-4 snap-mandatory snap-x scroll-pl-4 overflow-x-scroll no-scrollbar xl:safe-area-inset-scroll">
                                @foreach ($period->recapComparisons() as $recapComparison)
                                    <x-lockups.recap-comparison-lockup
                                        :recap="$recapComparison['recap']"
                                        :title="$recapComparison['title']"
                                        :subtitle="$recapComparison['subtitle']"
                                        :current-model="$recapComparison['currentModel']"
                                        :current-detail="$recapComparison['currentDetail']"
                                        :previous-model="$recapComparison['previousModel']"
                                        :previous-detail="$recapComparison['previousDetail']"
                                        :share-key="'comparison-' . $loop->index"
                                    />
                                @endforeach
                            </div>
                        </section>
                    @endif

                    <section class="mt-12">
                        <div class="xl:safe-area-inset">
                            <h2 class="pt-10 pl-4 pr-4 pb-10 text-secondary text-2xl font-semibold">{{ __('These milestones marked your season finale') }}</h2>
                        </div>

                        <section class="flex flex-nowrap gap-4 mt-2 pt-2 pl-4 pr-4 pb-4 snap-mandatory snap-x scroll-pl-4 overflow-x-scroll no-scrollbar xl:safe-area-inset-scroll">
                            @foreach ($period->recaps() as $recap)
                                @switch($recap->type)
                                    @case(\App\Models\Anime::class)
                                        @if ($recap->total_parts_duration)
                                            <x-lockups.milestone-lockup
                                                :recap="$recap"
                                                :title="__('Minutes Watched')"
                                                :progress-aria-label="__(':x Minutes', ['x' => number_format(round_to_nearest_quarter($recap->total_parts_duration / 60))])"
                                                :progress-count="number_format(round_to_nearest_quarter($recap->total_parts_duration / 60))"
                                                :progress-unit="__('Minutes')"
                                            />
                                        @endif

                                        @if ($recap->total_parts_count)
                                            <x-lockups.milestone-lockup
                                                :recap="$recap"
                                                :title="__('Episodes Watched')"
                                                :progress-aria-label="__(':x Episodes', ['x' => number_format(round_to_nearest_quarter($recap->total_parts_count))])"
                                                :progress-count="number_format(round_to_nearest_quarter($recap->total_parts_count))"
                                                :progress-unit="__('Episodes')"
                                                :media-collection="\App\Enums\MediaCollection::Banner"
                                            />
                                        @endif
                                        @break
                                    @case(\App\Models\Manga::class)
                                        @if ($recap->total_parts_duration)
                                            <x-lockups.milestone-lockup
                                                :recap="$recap"
                                                :title="__('Minutes Read')"
                                                :progress-aria-label="__(':x Minutes', ['x' => number_format(round_to_nearest_quarter($recap->total_parts_duration / 60))])"
                                                :progress-count="number_format(round_to_nearest_quarter($recap->total_parts_duration / 60))"
                                                :progress-unit="__('Minutes')"
                                            />
                                        @endif

                                        @if ($recap->total_parts_count)
                                            <x-lockups.milestone-lockup
                                                :recap="$recap"
                                                :title="__('Chapters Read')"
                                                :progress-aria-label="__(':x Chapters', ['x' => number_format(round_to_nearest_quarter($recap->total_parts_count))])"
                                                :progress-count="number_format(round_to_nearest_quarter($recap->total_parts_count))"
                                                :progress-unit="__('Chapters')" />
                                        @endif
                                        @break
                                    @case(\App\Models\Game::class)
                                        @if ($recap->total_parts_duration)
                                            <x-lockups.milestone-lockup
                                                :recap="$recap"
                                                :title="__('Minutes Played')"
                                                :progress-aria-label="__(':x Minutes', ['x' => number_format(round_to_nearest_quarter($recap->total_parts_duration / 60))])"
                                                :progress-count="number_format(round_to_nearest_quarter($recap->total_parts_duration / 60))"
                                                :progress-unit="__('Minutes')"
                                            />
                                        @endif

                                        @if ($recap->total_parts_count)
                                            <x-lockups.milestone-lockup
                                                :recap="$recap"
                                                :title="str(__('Games played'))->title()"
                                                :progress-aria-label="__(':x Games', ['x' => number_format(round_to_nearest_quarter($recap->total_parts_count))])"
                                                :progress-count="number_format(round_to_nearest_quarter($recap->total_parts_count))"
                                                :progress-unit="__('Games')"
                                            />
                                        @endif
                                        @break
                                    @default
                                @endswitch
                            @endforeach
                        </section>

                        @php($recap = $period->recaps()->where('top_percentile', '!=', 0.00)->sortBy('top_percentile')->first())
                        @if (!empty($recap))
                            <section class="mt-2 xl:safe-area-inset">
                                <div class="flex flex-col gap-4 pl-4 pr-4">
                                @switch($recap->type)
                                    @case(\App\Models\Anime::class)
                                        <x-lockups.milestone-lockup
                                            class="pb-5"
                                            style="min-width: 100%; max-width: 100%;"
                                            :recap="null"
                                            :title="__('You were in the top :x% of anime watchers this year.', ['x' => round($recap->top_percentile / 0.05) * 0.05])"
                                            :progress-aria-label="__('Top :x% of anime watchers', ['x' => round($recap->top_percentile / 0.05) * 0.05])"
                                            :progress-count="round($recap->top_percentile / 0.05) * 0.05 . '%'"
                                            :progress-unit="__('Top Anime Watcher')"
                                        />
                                        @break
                                    @case(\App\Models\Manga::class)
                                        <x-lockups.milestone-lockup
                                            class="pb-5"
                                            style="min-width: 100%; max-width: 100%;"
                                            :recap="null"
                                            :title="__('You were in the top :x% of manga readers this year.', ['x' => round($recap->top_percentile / 0.05) * 0.05])"
                                            :progress-aria-label="__('Top :x% of manga readers', ['x' => round($recap->top_percentile / 0.05) * 0.05])"
                                            :progress-count="round($recap->top_percentile / 0.05) * 0.05 . '%'"
                                            :progress-unit="__('Top Manga Reader')"
                                        />
                                        @break
                                    @case(\App\Models\Game::class)
                                        <x-lockups.milestone-lockup
                                            style="min-width: 100%; max-width: 100%;"
                                            :recap="null"
                                            :title="__('You were in the top :x% of game players this year.', ['x' => round($recap->top_percentile / 0.05) * 0.05])"
                                            :progress-aria-label="__('Top :x% of game players', ['x' => round($recap->top_percentile / 0.05) * 0.05])"
                                            :progress-count="round($recap->top_percentile / 0.05) * 0.05 . '%'"
                                            :progress-unit="__('Top Game Player')"
                                        />
                                        @break
                                    @default
                                @endswitch
                                </div>
                            </section>
                        @endif
                    </section>
                @elseif ($period->year === now()->year && $period->month === now()->month)
                    <div class="flex flex-col items-center justify-center xl:safe-area-inset" style="height: calc(100vh - 180px);">
                        <h2 class="max-w-sm pl-4 pr-4 text-center text-xl font-semibold md:max-w-2xl md:text-4xl">
                            {{ __(':x Re:CAP is still in progress. Check back in early :y.', ['x' => now()->monthName, 'y' => now()->addMonthNoOverflow()->monthName]) }}
                        </h2>

                        <x-link-button class="mt-12" href="{{ route('home') }}" wire:navigate>{{ __('Keep Tracking on :x', ['x' => config('app.name')]) }}</x-link-button>
                    </div>
                @endif
            </div>

            <div
                class="hidden absolute top-0 bottom-0 left-0 right-0 mx-auto mb-8 pl-5 pr-5 pb-6 text-primary z-10"
                data-loading
            >
                <x-recap-backdrop :recap="$period->backdropRecap()" />

                <div class="flex flex-col items-center justify-center w-full h-screen text-center xl:safe-area-inset">
                    <p class="animate-pulse text-5xl font-black">{{ __('This is your Re:CAP.') }}</p>
                </div>
            </div>

            <x-dialog-modal id="recap-canvas-access" maxWidth="sm">
                <x-slot:title>
                    {{ __('Allow Canvas Access') }}
                </x-slot:title>

                <x-slot:content>
                    <div class="pt-4 pb-4 pl-4 pr-4">
                        <p>{{ __('Your browser is blocking the canvas, which your Re:CAP image is drawn on. Allow canvas access when your browser asks, then try again.') }}</p>
                    </div>
                </x-slot:content>

                <x-slot:footer>
                    <x-outlined-button x-on:click="$dispatch('close')">
                        {{ __('Cancel') }}
                    </x-outlined-button>

                    <x-button class="ml-2" data-recap-share-retry>
                        {{ __('Try Again') }}
                    </x-button>
                </x-slot:footer>
            </x-dialog-modal>
        </div>
    </main>
</x-base-layout>
