<div wire:init="loadSection">
    @if ($readyToLoad)
        @switch ($type)
        @case ('drops')
            @if ($this->data['hero'])
                <section>
                    <x-lockups.banner-lockup :anime="$this->data['hero']['model']" />

                    <div class="xl:safe-area-inset">
                        <p class="pl-4 pr-4 pt-2 text-secondary">{{ $this->data['heroCaption'] }}</p>
                    </div>
                </section>
            @endif

            @if ($this->data['newEpisodes']->isNotEmpty())
                <section class="pt-4 pb-8">
                    <div class="xl:safe-area-inset">
                        <x-section-nav>
                            <x-slot:title>{{ __('New Episodes') }}</x-slot:title>
                        </x-section-nav>
                    </div>

                    <x-rows.episode-lockup :episodes="$this->data['newEpisodes']" />
                </section>
            @endif

            @if ($this->data['finales']->isNotEmpty())
                <section class="pt-4 pb-8">
                    <div class="xl:safe-area-inset">
                        <x-section-nav>
                            <x-slot:title>{{ __('Season Finales') }}</x-slot:title>
                            <x-slot:description>{{ __('These wrap up this week.') }}</x-slot:description>
                        </x-section-nav>
                    </div>

                    <x-rows.episode-lockup :episodes="$this->data['finales']" />
                </section>
            @endif

            @if ($this->data['newReleases']->isNotEmpty())
                <section class="pt-4 pb-8">
                    <div class="xl:safe-area-inset">
                        <x-section-nav>
                            <x-slot:title>{{ __('New Releases') }}</x-slot:title>
                        </x-section-nav>
                    </div>

                    <x-rows.small-lockup :games="$this->data['newReleases']" />
                </section>
            @endif
            @break
        @case ('recommendations')
            @if ($this->data['becauseYouWatched']['relations']->isNotEmpty())
                <section class="pt-4 pb-8">
                    <div class="xl:safe-area-inset">
                        <x-section-nav>
                            <x-slot:title>{{ __('Because You Watched :title', ['title' => $this->data['becauseYouWatched']['anime']->title]) }}</x-slot:title>
                        </x-section-nav>
                    </div>

                    <x-rows.small-lockup :related-animes="$this->data['becauseYouWatched']['relations']" />
                </section>
            @endif

            @if ($this->data['dropIn'])
                <section class="pt-4 pb-8">
                    <div class="xl:safe-area-inset">
                        <x-section-nav>
                            <x-slot:title>{{ __('A Weekend Watch For You') }}</x-slot:title>
                            <x-slot:description>{{ __('Highly rated, and not yet on your list.') }}</x-slot:description>
                        </x-section-nav>

                        <div class="pl-4 pr-4">
                            <x-lockups.small-lockup :anime="$this->data['dropIn']" :is-row="false" />
                        </div>
                    </div>
                </section>
            @endif
            @break
        @case ('rescue')
            @if ($this->data['onHold']->isNotEmpty())
                <section class="pt-4 pb-8">
                    <div class="xl:safe-area-inset">
                        <x-section-nav>
                            <x-slot:title>{{ __('Pick Up Where You Left Off') }}</x-slot:title>
                            <x-slot:description>{{ __('On hold for a while. Ready to continue?') }}</x-slot:description>
                        </x-section-nav>
                    </div>

                    <x-rows.small-lockup :animes="$this->data['onHold']" />
                </section>
            @endif

            @if ($this->data['planning']->isNotEmpty())
                <section class="pt-4 pb-8">
                    <div class="xl:safe-area-inset">
                        <x-section-nav>
                            <x-slot:title>{{ __('Ready to Start?') }}</x-slot:title>
                            <x-slot:description>{{ __('Sitting on your planning list for a while.') }}</x-slot:description>
                        </x-section-nav>
                    </div>

                    <x-rows.small-lockup :animes="$this->data['planning']" />
                </section>
            @endif
            @break
        @case ('up-next')
            @if ($this->data['premiering']->isNotEmpty())
                <section class="pt-4 pb-8">
                    <div class="xl:safe-area-inset">
                        <x-section-nav>
                            <x-slot:title>{{ __('Premiering Soon') }}</x-slot:title>
                        </x-section-nav>
                    </div>

                    <x-rows.small-lockup :animes="$this->data['premiering']" />
                </section>
            @endif

            @if ($this->data['releasing']->isNotEmpty())
                <section class="pt-4 pb-8">
                    <div class="xl:safe-area-inset">
                        <x-section-nav>
                            <x-slot:title>{{ __('Releasing Soon') }}</x-slot:title>
                        </x-section-nav>
                    </div>

                    <x-rows.small-lockup :games="$this->data['releasing']" />
                </section>
            @endif
            @break
        @case ('trending')
            @if ($this->data['trending']->isNotEmpty())
                <section class="pt-4 pb-8">
                    <div class="xl:safe-area-inset">
                        <x-section-nav>
                            <x-slot:title>{{ __('Trending This Week') }}</x-slot:title>
                        </x-section-nav>
                    </div>

                    <x-rows.episode-lockup :episodes="$this->data['trending']" />
                </section>
            @endif
            @break
        @case ('birthdays')
            @if ($this->data['birthdays']->isNotEmpty())
                <section class="pt-4 pb-8">
                    <div class="xl:safe-area-inset">
                        <x-section-nav>
                            <x-slot:title>{{ __('Birthdays This Week') }}</x-slot:title>
                            <x-slot:description>{{ __('From the people making your favorite titles.') }}</x-slot:description>
                        </x-section-nav>
                    </div>

                    <x-rows.person-lockup :people="$this->data['birthdays']" />
                </section>
            @endif
            @break
        @case ('momentum')
            @if ($this->data['hasMomentum'])
                <section class="pt-4 pb-8">
                    <div class="xl:safe-area-inset">
                        <div class="flex flex-col items-center gap-6 bg-secondary pt-8 pb-8 pl-6 pr-6 text-center">
                            <p class="text-secondary">{{ __('Your week in numbers') }}</p>

                            <div class="flex flex-wrap justify-center gap-8">
                                @if ($this->data['momentum']['episodesWatched'] > 0)
                                    <div>
                                        <p class="text-4xl font-bold">{{ number_format($this->data['momentum']['episodesWatched']) }}</p>
                                        <p class="text-secondary">{{ __('episodes watched') }}</p>
                                    </div>

                                    @if ($this->data['watchedTime'])
                                        <div>
                                            <p class="text-4xl font-bold">{{ $this->data['watchedTime'] }}</p>
                                            <p class="text-secondary">{{ __('watched') }}</p>
                                        </div>
                                    @endif
                                @endif

                                @if ($this->data['momentum']['finishedCount'] > 0)
                                    <div>
                                        <p class="text-4xl font-bold">{{ number_format($this->data['momentum']['finishedCount']) }}</p>
                                        <p class="text-secondary">{{ __('titles finished') }}</p>
                                    </div>
                                @endif
                            </div>

                            @if ($this->data['milestone'])
                                <p class="font-semibold">{{ $this->data['milestone'] }}</p>
                            @endif

                            @if ($this->data['streak'])
                                <p class="font-semibold">{{ $this->data['streak'] }}</p>
                            @endif

                            <a
                                href="{{ route('recap.index') }}"
                                wire:navigate
                                class="inline-flex items-center rounded-md bg-tint pl-6 pr-6 pt-2 pb-2 font-semibold btn-text-tinted"
                            >
                                {{ __('See Your Re:CAP') }}
                            </a>
                        </div>
                    </div>
                </section>
            @endif
            @break
        @case ('growth')
            @if ($this->data['hasGrowth'])
                <section class="pb-8 xl:safe-area-inset">
                    <p class="pl-4 pr-4 text-center text-sm text-secondary">{{ $this->data['label'] }}</p>
                </section>
            @endif
            @break
        @endswitch
    @else
        <section class="pt-4 pb-8 xl:safe-area-inset">
            <div class="pl-4 pr-4">
                <p class="bg-secondary rounded-md mb-5" style="width: 12rem; height: 1.5rem;"></p>

                <div class="flex flex-nowrap gap-4 overflow-hidden">
                    @foreach (range(1, 4) as $skeletonCard)
                        <div class="bg-secondary rounded-lg w-64 shrink-0 md:w-80" style="height: 168px;"></div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>
