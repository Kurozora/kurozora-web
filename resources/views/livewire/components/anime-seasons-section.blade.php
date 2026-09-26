<div>
    @if ($this->seasons->count())
        <section class="pb-8">
            <x-section-nav class="pt-4 xl:safe-area-inset-scroll">
                <x-slot:title>
                    {{ __('Seasons') }}
                </x-slot:title>

                <x-slot:action>
                    @hasrole('superAdmin')
                        <x-button wire:click="$refresh">{{ __('Refresh') }}</x-button>
                    @endhasrole
                    <x-section-nav-link href="{{ route('anime.seasons', $anime) }}">{{ __('See All') }}</x-section-nav-link>
                </x-slot:action>
            </x-section-nav>

            <div class="flex flex-nowrap gap-4 justify-start pl-4 pr-4 snap-mandatory snap-x scroll-pl-4 overflow-x-scroll no-scrollbar xl:safe-area-inset-scroll">
                @foreach ($this->seasons as $season)
                    <x-lockups.season-lockup :season="$season" />
                @endforeach
            </div>
        </section>
    @endif
</div>
