<div>
    @if ($seasonsCount)
        <section class="pt-5 pb-8 px-4 border-t-2" wire:init="loadAnimeSeasons">
            <x-section-nav>
                <x-slot:title>
                    {{ __('Seasons') }}
                </x-slot>

                <x-slot:action>
                    <x-section-nav-link href="{{ route('anime.seasons', $anime) }}">{{ __('See All') }}</x-section-nav-link>
                </x-slot>
            </x-section-nav>

            <div class="flex justify-center">
                <x-spinner />
            </div>

            <div class="grid grid-flow-col-dense gap-4 justify-start overflow-x-scroll no-scrollbar">
                @foreach($seasons as $season)
                    <x-lockups.poster-lockup :season="$season" />
                @endforeach
            </div>
        </section>
    @endif
</div>
