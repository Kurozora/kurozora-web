<div wire:init="loadSection">
    @if ($this->gameRelations->count())
        <section class="pt-5 pb-8 pl-4 pr-4 border-t border-primary">
            <x-section-nav>
                <x-slot:title>
                    {{ __('Games') }}
                </x-slot:title>

                <x-slot:action>
                    @hasrole('superAdmin')
                        <x-button wire:click="$refresh">{{ __('Refresh') }}</x-button>
                    @endhasrole
                    <x-section-nav-link href="{{ route('manga.related-games', $manga) }}">{{ __('See All') }}</x-section-nav-link>
                </x-slot:action>
            </x-section-nav>

            <x-rows.small-lockup :related-games="$this->gameRelations" />
        </section>
    @elseif (!$readyToLoad)
        <section class="pt-5 pb-8 pl-4 pr-4 border-t border-primary">
            <div class="flex gap-2 justify-between mb-5">
                <div>
                    <p class="bg-secondary rounded-md" style="width: 168px; height: 28px"></p>
                    <p class="bg-secondary rounded-md" style="width: 228px; height: 22px"></p>
                </div>

                <div class="flex flex-wrap gap-2 justify-end"></div>
            </div>

            <div class="flex gap-4 justify-between snap-mandatory snap-x overflow-x-scroll no-scrollbar">
                <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
            </div>
        </section>
    @endif
</div>
