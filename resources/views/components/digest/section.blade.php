<div>
    @switch ($type)
    @case ('drops')
        @if ($section['hero'])
            <section>
                <x-lockups.banner-lockup :anime="$section['hero']['model']" />

                <div class="xl:safe-area-inset">
                    <p class="pl-4 pr-4 pt-2 text-secondary">{{ $section['heroCaption'] }}</p>
                </div>
            </section>
        @endif

        @if ($section['newEpisodes']->isNotEmpty())
            <section class="pt-4 pb-8">
                <div class="xl:safe-area-inset">
                    <x-section-nav>
                        <x-slot:title>{{ __('New Episodes') }}</x-slot:title>
                    </x-section-nav>
                </div>

                <x-rows.episode-lockup :episodes="$section['newEpisodes']" />
            </section>
        @endif

        @if ($section['finales']->isNotEmpty())
            <section class="pt-4 pb-8">
                <div class="xl:safe-area-inset">
                    <x-section-nav>
                        <x-slot:title>{{ __('Season Finales') }}</x-slot:title>
                        <x-slot:description>{{ __('These wrap up this week.') }}</x-slot:description>
                    </x-section-nav>
                </div>

                <x-rows.episode-lockup :episodes="$section['finales']" />
            </section>
        @endif

        @if ($section['newReleases']->isNotEmpty())
            <section class="pt-4 pb-8">
                <div class="xl:safe-area-inset">
                    <x-section-nav>
                        <x-slot:title>{{ __('New Releases') }}</x-slot:title>
                    </x-section-nav>
                </div>

                <x-rows.small-lockup :games="$section['newReleases']" />
            </section>
        @endif
        @break
    @case ('recommendations')
        @if ($section['becauseYouWatched']['relations']->isNotEmpty())
            <section class="pt-4 pb-8">
                <div class="xl:safe-area-inset">
                    <x-section-nav>
                        <x-slot:title>{{ __('Because You Watched :title', ['title' => $section['becauseYouWatched']['anime']->title]) }}</x-slot:title>
                    </x-section-nav>
                </div>

                <x-rows.small-lockup :related-animes="$section['becauseYouWatched']['relations']" />
            </section>
        @endif

        @if ($section['dropIn'])
            <section class="pt-4 pb-8">
                <div class="xl:safe-area-inset">
                    <x-section-nav>
                        <x-slot:title>{{ __('A Weekend Watch For You') }}</x-slot:title>
                        <x-slot:description>{{ __('Highly rated, and not yet on your list.') }}</x-slot:description>
                    </x-section-nav>

                    <div class="pl-4 pr-4">
                        <x-lockups.small-lockup :anime="$section['dropIn']" :is-row="false" />
                    </div>
                </div>
            </section>
        @endif
        @break
    @case ('rescue')
        @if ($section['onHold']->isNotEmpty())
            <section class="pt-4 pb-8">
                <div class="xl:safe-area-inset">
                    <x-section-nav>
                        <x-slot:title>{{ __('Pick Up Where You Left Off') }}</x-slot:title>
                        <x-slot:description>{{ __('On hold for a while. Ready to continue?') }}</x-slot:description>
                    </x-section-nav>
                </div>

                <x-rows.small-lockup :animes="$section['onHold']" />
            </section>
        @endif

        @if ($section['planning']->isNotEmpty())
            <section class="pt-4 pb-8">
                <div class="xl:safe-area-inset">
                    <x-section-nav>
                        <x-slot:title>{{ __('Ready to Start?') }}</x-slot:title>
                        <x-slot:description>{{ __('Sitting on your planning list for a while.') }}</x-slot:description>
                    </x-section-nav>
                </div>

                <x-rows.small-lockup :animes="$section['planning']" />
            </section>
        @endif
        @break
    @case ('up-next')
        @if ($section['premiering']->isNotEmpty())
            <section class="pt-4 pb-8">
                <div class="xl:safe-area-inset">
                    <x-section-nav>
                        <x-slot:title>{{ __('Premiering Soon') }}</x-slot:title>
                    </x-section-nav>
                </div>

                <x-rows.small-lockup :animes="$section['premiering']" />
            </section>
        @endif

        @if ($section['releasing']->isNotEmpty())
            <section class="pt-4 pb-8">
                <div class="xl:safe-area-inset">
                    <x-section-nav>
                        <x-slot:title>{{ __('Releasing Soon') }}</x-slot:title>
                    </x-section-nav>
                </div>

                <x-rows.small-lockup :games="$section['releasing']" />
            </section>
        @endif
        @break
    @case ('trending')
        @if ($section['trending']->isNotEmpty())
            <section class="pt-4 pb-8">
                <div class="xl:safe-area-inset">
                    <x-section-nav>
                        <x-slot:title>{{ __('Trending This Week') }}</x-slot:title>
                    </x-section-nav>
                </div>

                <x-rows.episode-lockup :episodes="$section['trending']" />
            </section>
        @endif
        @break
    @case ('birthdays')
        @if ($section['birthdays']->isNotEmpty())
            <section class="pt-4 pb-8">
                <div class="xl:safe-area-inset">
                    <x-section-nav>
                        <x-slot:title>{{ __('Birthdays This Week') }}</x-slot:title>
                        <x-slot:description>{{ __('From the people making your favorite titles.') }}</x-slot:description>
                    </x-section-nav>
                </div>

                <x-rows.person-lockup :people="$section['birthdays']" />
            </section>
        @endif
        @break
    @case ('momentum')
        @if ($section['hasMomentum'])
            <section class="pt-4 pb-8">
                <div class="xl:safe-area-inset">
                    <div class="flex flex-col items-center gap-6 bg-secondary pt-8 pb-8 pl-6 pr-6 text-center">
                        <p class="text-secondary">{{ __('Your week in numbers') }}</p>

                        <div class="flex flex-wrap justify-center gap-8">
                            @if ($section['momentum']['episodesWatched'] > 0)
                                <div>
                                    <p class="text-4xl font-bold">{{ number_format($section['momentum']['episodesWatched']) }}</p>
                                    <p class="text-secondary">{{ str(__('Episodes Watched'))->lower() }}</p>
                                </div>

                                @if ($section['watchedTime'])
                                    <div>
                                        <p class="text-4xl font-bold">{{ $section['watchedTime'] }}</p>
                                        <p class="text-secondary">{{ str(__('Watched'))->lower() }}</p>
                                    </div>
                                @endif
                            @endif

                            @if ($section['momentum']['finishedCount'] > 0)
                                <div>
                                    <p class="text-4xl font-bold">{{ number_format($section['momentum']['finishedCount']) }}</p>
                                    <p class="text-secondary">{{ __('titles finished') }}</p>
                                </div>
                            @endif
                        </div>

                        @if ($section['milestone'])
                            <p class="font-semibold">{{ $section['milestone'] }}</p>
                        @endif

                        @if ($section['streak'])
                            <p class="font-semibold">{{ $section['streak'] }}</p>
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
        @if ($section['hasGrowth'])
            <section class="pb-8 xl:safe-area-inset">
                <p class="pl-4 pr-4 text-center text-sm text-secondary">{{ $section['label'] }}</p>
            </section>
        @endif
        @break
    @endswitch
</div>
