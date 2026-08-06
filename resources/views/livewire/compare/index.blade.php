<main>
    <x-slot:title>
        {{ __(':x vs. MyAnimeList, AniList, Kitsu & Other Anime Trackers', ['x' => config('app.name')]) }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Comparing the best anime tracking apps and websites: :x, MyAnimeList, AniList, Kitsu, AniDB, Simkl, Trakt, and more. Every feature side by side, including which trackers have no ads.', ['x' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __(':x vs. MyAnimeList, AniList, Kitsu & Other Anime Trackers', ['x' => config('app.name')]) }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Comparing the best anime tracking apps and websites: :x, MyAnimeList, AniList, Kitsu, AniDB, Simkl, Trakt, and more. Every feature side by side, including which trackers have no ads.', ['x' => config('app.name')]) }}" />
        <link rel="canonical" href="{{ route('compare.index') }}">
    </x-slot:meta>

    <x-slot:appArgument>
        compare
    </x-slot:appArgument>

    <div class="pt-4 pb-6">
        <section class="xl:safe-area-inset">
            <div class="pl-4 pr-4">
                <section class="m-auto max-w-4xl prose prose-theme lg:prose-lg">
                    <div class="text-center">
                        <h1>{{ __('How does :x compare?', ['x' => config('app.name')]) }}</h1>
                        <p>{{ __(':x is a free tracker without ads. It sends a push when episodes air, knows the lyrics to your favorite openings, and recaps your year in anime. Here is how it stacks up against the services people usually compare it with.', ['x' => config('app.name')]) }}</p>
                    </div>

                    <x-hr />
                </section>

                <div class="flex flex-wrap items-center justify-end gap-2 pt-6 pb-2 text-sm">
                    <x-simple-link href="{{ $this->reportProblemUrl }}" target="_blank">{{ __('Report problems with this comparison data') }}</x-simple-link>
                    <span class="text-secondary">•</span>
                    <x-simple-link href="{{ $this->dataFileUrl }}" target="_blank">{{ __('View data on GitHub') }}</x-simple-link>
                </div>

                <div
                    class="overflow-x-auto rounded-lg border border-primary"
                    x-data="{ openNote: null, noteWidth: 0, isMd: true }"
                    x-init="isMd = window.matchMedia('(min-width: 768px)').matches; noteWidth = $el.clientWidth"
                    x-on:resize.window="isMd = window.matchMedia('(min-width: 768px)').matches; noteWidth = $el.clientWidth"
                >
                    <table
                        class="table-fixed text-sm border-separate border-spacing-0"
                        style="width: {{ 16 + count($this->services) * 10 }}rem;"
                        x-bind:style="'width: ' + (isMd ? 16 + {{ count($this->services) }} * 10 : 8 + {{ count($this->services) }} * 8) + 'rem'"
                    >
                        <colgroup>
                            <col class="w-32 md:w-64">

                            @foreach ($this->services as $service)
                                <col class="w-32 md:w-40">
                            @endforeach
                        </colgroup>

                        <thead>
                            <tr>
                                <th scope="col" class="sticky left-0 z-10 bg-secondary pl-4 pr-4 pt-3 pb-3 text-left font-semibold"></th>

                                @foreach ($this->services as $service)
                                    <th scope="col" class="{{ $loop->first ? 'sticky left-32 md:left-64 z-10 border-r border-primary text-tint' : '' }} bg-secondary pl-4 pr-4 pt-3 pb-3 text-center font-semibold">
                                        {{ $service['name'] }}

                                        @if (!empty($service['note']))
                                            <p class="text-xs text-secondary font-normal">{{ $service['note'] }}</p>
                                        @endif
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($this->featureRows as $featureRow)
                                @php $rowIndex = $loop->index; @endphp

                                <tr
                                    class="cursor-pointer"
                                    x-on:click="openNote = openNote === {{ $rowIndex }} ? null : {{ $rowIndex }}"
                                >
                                    <th scope="row" class="sticky left-0 z-10 bg-primary border-t border-primary p-0 text-left font-normal">
                                        <button
                                            class="flex w-full pl-4 pr-4 pt-3 pb-3 text-left cursor-pointer"
                                            x-bind:aria-expanded="openNote === {{ $rowIndex }}"
                                        >
                                            {{ $featureRow['label'] }}
                                            <span class="sr-only">{{ __('Show details') }}</span>
                                        </button>
                                    </th>

                                    @foreach ($featureRow['cells'] as $featureCell)
                                        <td class="{{ $loop->first ? 'sticky left-32 md:left-64 z-10 bg-primary border-r' : '' }} border-t border-primary pl-4 pr-4 pt-3 pb-3 text-center">
                                            <x-compare.support-value :display="$featureCell['display']" />
                                        </td>
                                    @endforeach
                                </tr>

                                <tr x-cloak x-show="openNote === {{ $rowIndex }}">
                                    <td colspan="{{ count($this->services) + 1 }}" class="pt-2 pb-2 bg-secondary border-t border-primary text-xs text-left">
                                        <div
                                            class="sticky left-0 flex flex-col gap-1 pl-4 pr-4"
                                            x-bind:style="'max-width: ' + noteWidth + 'px'"
                                        >
                                            @if (!empty($featureRow['description']))
                                                <p>{{ $featureRow['description'] }}</p>
                                            @endif

                                            @foreach ($featureRow['notes'] as $featureNote)
                                                <p><span class="font-semibold">{{ $featureNote['service'] }}:</span> {{ $featureNote['note'] }}</p>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <p class="pt-2 text-sm text-secondary">{{ __('Tip: click a row for more information.') }}</p>

                <section class="m-auto max-w-4xl pt-8 prose prose-theme lg:prose-lg">
                    <x-hr />

                    <h2 class="text-center">{{ __('Service by Service') }}</h2>

                    @foreach ($this->serviceComparisons as $serviceComparison)
                        <h4>{{ $serviceComparison['title'] }}</h4>
                        <p>{{ $serviceComparison['paragraph'] }}</p>
                    @endforeach

                    <x-hr />

                    <h2>{{ __('Frequently Asked Questions') }}</h2>

                    @foreach ($this->frequentlyAskedQuestions as $frequentlyAskedQuestion)
                        <h4>{{ $frequentlyAskedQuestion['question'] }}</h4>
                        <p>{{ $frequentlyAskedQuestion['answer'] }}</p>
                    @endforeach
                </section>

                <div class="flex flex-col items-center gap-4 pt-6 pb-6 text-center max-w-2xl mx-auto">
                    <p class="text-sm text-secondary">{{ __('The :x website and app share the same features. Each platform adds its own extras, like push notifications in the app when new episodes air.', ['x' => config('app.name')]) }}</p>

                    <div class="flex flex-wrap items-center justify-center gap-2">
                        @guest
                            <x-link-button class="whitespace-nowrap" href="{{ route('sign-up') }}" wire:navigate>{{ __('Sign up for free') }}</x-link-button>
                        @endguest

                        <x-link-button class="whitespace-nowrap" href="{{ route('welcome') }}" wire:navigate>{{ __('Discover :x', ['x' => config('app.name')]) }}</x-link-button>

                        <x-link-button class="whitespace-nowrap" href="{{ config('app.ios.store_url') }}" target="_blank">{{ __('Get the App') }}</x-link-button>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>
