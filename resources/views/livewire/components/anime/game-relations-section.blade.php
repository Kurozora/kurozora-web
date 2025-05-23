<div wire:init="loadSection">
    @if ($this->gameRelations->count())
        <section class="pb-8">
                <x-hr class="ml-4 mr-4 pb-5" />

            <x-section-nav>
                <x-slot:title>
                    {{ __('Games') }}
                </x-slot:title>

                <x-slot:action>
                    @hasrole('superAdmin')
                        <x-button wire:click="$refresh">{{ __('Refresh') }}</x-button>
                    @endhasrole
                    <x-section-nav-link href="{{ route('anime.related-games', $anime) }}">{{ __('See All') }}</x-section-nav-link>
                </x-slot:action>
            </x-section-nav>

            <x-rows.small-lockup :related-games="$this->gameRelations" />
        </section>
    @elseif (!$readyToLoad)
        <section class="pb-8">
                <x-hr class="ml-4 mr-4 pb-5" />

            <div class="flex gap-2 justify-between mb-5 pl-4 pr-4">
                <div>
                    <p class="bg-secondary rounded-md" style="width: 168px; height: 28px"></p>
                    <p class="bg-secondary rounded-md" style="width: 228px; height: 22px"></p>
                </div>

                <div class="flex flex-wrap gap-2 justify-end"></div>
            </div>

            <div class="flex gap-4 justify-between pl-4 pr-4 snap-mandatory snap-x overflow-x-scroll no-scrollbar">
                <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
            </div>
        </section>
    @endif
</div>
